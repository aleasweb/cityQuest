const DB_NAME = 'OpenSpecDBCQ';
const STORE_NAME = 'handles';

// --- IndexedDB Wrapper ---
function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = (e) => {
            e.target.result.createObjectStore(STORE_NAME);
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function getSavedHandle() {
    try {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const req = tx.objectStore(STORE_NAME).get('openspec_dir');
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    } catch (e) {
        console.error("IndexedDB error", e);
        return null;
    }
}

async function saveHandle(handle) {
    try {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const req = tx.objectStore(STORE_NAME).put(handle, 'openspec_dir');
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
        });
    } catch (e) {
        console.error("Failed to save handle", e);
    }
}

// --- File System Logic ---
let rootHandle = null;
let cachedSpecsTree = null;
let cachedArchiveTree = null;

async function init() {
    const grantBtn = document.getElementById('grant-btn');
    const grantHint = document.getElementById('grant-hint');
    
    rootHandle = await getSavedHandle();
    
    if (rootHandle) {
        grantBtn.textContent = 'Разрешить чтение папки openspec';
        grantHint.style.display = 'none';
        grantBtn.onclick = async () => {
            // Запрашиваем права на сохраненный handle
            const permission = await rootHandle.requestPermission({ mode: 'read' });
            if (permission === 'granted') {
                await loadApp();
            } else {
                alert("Необходимо разрешить доступ к файлам.");
            }
        };
    } else {
        grantBtn.textContent = 'Выбрать папку openspec (первый запуск)';
        grantHint.style.display = 'block';
        grantBtn.onclick = async () => {
            try {
                // Первый запуск - просим выбрать директорию
                rootHandle = await window.showDirectoryPicker({ mode: 'read' });
                await saveHandle(rootHandle);
                await loadApp();
            } catch (e) {
                console.error("User cancelled or error", e);
            }
        };
    }
}

async function loadApp() {
    document.getElementById('setup-screen').classList.add('hidden');
    document.getElementById('app-screen').classList.remove('hidden');
    
    // Загружаем вкладку Specs по умолчанию
    await switchTab('specs');
    
    // Настраиваем переключение вкладок
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            switchTab(e.target.dataset.tab);
        });
    });
}

async function switchTab(tabId) {
    const container = document.getElementById('tree-container');
    container.innerHTML = '<div style="padding: 10px; color: #666;">Загрузка...</div>';
    
    try {
        if (tabId === 'specs') {
            if (!cachedSpecsTree) {
                const specsHandle = await rootHandle.getDirectoryHandle('specs', { create: false });
                cachedSpecsTree = await buildTree(specsHandle);
            }
            container.innerHTML = '';
            container.appendChild(cachedSpecsTree);
        } else if (tabId === 'archive') {
            if (!cachedArchiveTree) {
                const changesHandle = await rootHandle.getDirectoryHandle('changes', { create: false });
                const archiveHandle = await changesHandle.getDirectoryHandle('archive', { create: false });
                cachedArchiveTree = await buildTree(archiveHandle);
            }
            container.innerHTML = '';
            container.appendChild(cachedArchiveTree);
        }
    } catch (e) {
        console.error(e);
        container.innerHTML = `<div style="padding: 10px; color: red;">Ошибка: папка не найдена.<br>Убедитесь, что вы выбрали корневую папку <b>openspec</b>.</div>`;
    }
}

async function buildTree(dirHandle) {
    const items = [];
    for await (const entry of dirHandle.values()) {
        if (entry.name.startsWith('.')) continue; // Пропускаем скрытые файлы
        items.push(entry);
    }
    
    // Сортировка: сначала папки, потом файлы (по алфавиту)
    items.sort((a, b) => {
        if (a.kind === b.kind) return a.name.localeCompare(b.name);
        return a.kind === 'directory' ? -1 : 1;
    });

    const ul = document.createElement('ul');
    for (const entry of items) {
        const li = document.createElement('li');
        li.className = entry.kind;
        
        const label = document.createElement('span');
        label.textContent = entry.name;
        li.appendChild(label);

        if (entry.kind === 'directory') {
            const childrenContainer = document.createElement('div');
            childrenContainer.className = 'children hidden';
            
            label.onclick = async (e) => {
                e.stopPropagation();
                const isHidden = childrenContainer.classList.contains('hidden');
                
                // Ленивая загрузка дерева при первом клике
                if (isHidden && !childrenContainer.hasChildNodes()) {
                    const subTree = await buildTree(entry);
                    childrenContainer.appendChild(subTree);
                }
                
                childrenContainer.classList.toggle('hidden');
                label.classList.toggle('open');
            };
            li.appendChild(childrenContainer);
        } else if (entry.kind === 'file' && entry.name.endsWith('.md')) {
            label.onclick = async (e) => {
                e.stopPropagation();
                // Подсвечиваем активный файл
                document.querySelectorAll('.file span').forEach(el => el.classList.remove('active'));
                label.classList.add('active');
                await loadFile(entry);
            };
        } else {
             // Игнорируем не md файлы, делаем полупрозрачными
             label.style.opacity = 0.5;
             label.style.cursor = 'default';
        }
        ul.appendChild(li);
    }
    return ul;
}

async function loadFile(fileHandle) {
    const contentDiv = document.getElementById('content');
    contentDiv.innerHTML = '<div class="empty-state">Чтение файла...</div>';
    
    try {
        const file = await fileHandle.getFile();
        const text = await file.text();
        
        // Рендер Markdown (используем marked.js, подключенный в index.html)
        contentDiv.innerHTML = marked.parse(text);
    } catch (e) {
        console.error(e);
        contentDiv.innerHTML = '<div class="empty-state" style="color: red;">Не удалось прочитать файл.</div>';
    }
}

// Запуск инициализации при загрузке страницы
document.addEventListener('DOMContentLoaded', init);
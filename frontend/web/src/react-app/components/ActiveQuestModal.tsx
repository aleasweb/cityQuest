import { X, AlertTriangle } from 'lucide-react';

interface ActiveQuestModalProps {
  isOpen: boolean;
  onClose: () => void;
  activeQuestTitle?: string;
  onGoToQuest?: () => void;
}

export default function ActiveQuestModal({
  isOpen,
  onClose,
  activeQuestTitle,
  onGoToQuest,
}: ActiveQuestModalProps) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black bg-opacity-50"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        {/* Close button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
        >
          <X className="w-5 h-5" />
        </button>

        {/* Icon */}
        <div className="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-full mx-auto mb-4">
          <AlertTriangle className="w-6 h-6 text-orange-600" />
        </div>

        {/* Title */}
        <h2 className="text-xl font-bold text-gray-900 text-center mb-2">
          У вас уже есть активный квест
        </h2>

        {/* Message */}
        <p className="text-gray-600 text-center mb-6">
          {activeQuestTitle ? (
            <>
              Вы можете иметь только один активный квест.
              <br />
              Текущий: <span className="font-semibold">{activeQuestTitle}</span>
            </>
          ) : (
            'Вы можете иметь только один активный квест одновременно.'
          )}
        </p>

        {/* Actions */}
        <div className="flex gap-3">
          {onGoToQuest && (
            <button
              onClick={onGoToQuest}
              className="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg transition-colors"
            >
              Перейти к квесту
            </button>
          )}
          <button
            onClick={onClose}
            className="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors"
          >
            Закрыть
          </button>
        </div>
      </div>
    </div>
  );
}


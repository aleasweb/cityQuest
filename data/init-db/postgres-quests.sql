CREATE TABLE users
(
    id       SERIAL PRIMARY KEY,
    email    VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100)
);

CREATE TABLE quests
(
    id          SERIAL PRIMARY KEY,
    user_id     INT          NOT NULL,
    title       VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id)
);
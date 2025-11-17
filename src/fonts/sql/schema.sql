CREATE TABLE "user" (
    "id" INT AUTO_INCREMENT PRIMARY KEY,
    "username" VARCHAR(63) NOT NULL UNIQUE,
    "displaynamename" VARCHAR(63) NOT NULL UNIQUE,
    "is_studying" VARCHAR(10) NOT NULL,
    "password_user" VARCHAR(63) NOT NULL
)

CREATE TABLE "chat_participants"(
    "id" INT AUTO_INCREMENT PRIMARY KEY,
    "user_id" INT,
    "chat_id" INT,
    FOREIGN KEY ("user_id") REFERENCES user(id),
    FOREIGN KEY (chat_id) REFERENCES chat(id),
    --UNIQUE (user_id, chat_id)
)

CREATE TABLE chat(
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_name VARCHAR(63) NOT NULL,
    is course_of_study BOOLEAN,
    is_global BOOLEAN,
    is_group BOOLEAN,
    is_personal BOOLEAN,
)

CREATE TABLE message(
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT,
    sender_id INT,
    date_send DATE,
    content TEXT,
    FOREIGN KEY (chat_id) REFERENCES chat(id),
    FOREIGN KEY (sender_id) REFERENCES user(id)
)
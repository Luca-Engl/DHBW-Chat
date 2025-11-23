-- User
CREATE TABLE "user" (
    "id" INT AUTO_INCREMENT PRIMARY KEY,
    "username" VARCHAR(30) NOT NULL UNIQUE,
    "email" VARCHAR(100) NOT NULL UNIQUE,
    "password_hash" VARCHAR(255) NOT NULL,
    --"avatar_path" VARCHAR(255) DEFAULT 'img/default-avatar.png',

    "faculty" ENUM('T', 'W', 'S', 'G', 'A') DEFAULT NULL,
    "course" VARCHAR(10) DEFAULT NULL,
    "year" YEAR DEFAULT NULL,

    "is_guest" BOOLEAN DEFAULT FALSE,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chats (Globalchat, DMs, Groupchats)
CREATE TABLE "chat" (
    "id" INT AUTO_INCREMENT PRIMARY KEY,
    "chat_name" VARCHAR(100) NOT NULL,
    "chat_type" ENUM('global', 'personal', 'group', 'course') NOT NULL,
    "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- chat participants
CREATE TABLE "chat_participant" (
    "id" INT AUTO_INCREMENT PRIMARY KEY,
    "user_id" INT NOT NULL,
    "chat_id" INT NOT NULL,
    "joined_at" DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("user_id") REFERENCES "user"("id") ON DELETE CASCADE,
    FOREIGN KEY ("chat_id") REFERENCES "chat"("id") ON DELETE CASCADE,
    UNIQUE KEY "unique_user_chat" ("user_id", "chat_id")
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message(
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT,
    sender_id INT,
    date_send DATE,
    content TEXT,
    is_note BOOLEAN,
    FOREIGN KEY (chat_id) REFERENCES chat(id),
    FOREIGN KEY (sender_id) REFERENCES user(id)
)

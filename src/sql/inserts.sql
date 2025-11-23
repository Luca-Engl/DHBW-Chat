-- Insert new User
-- those are just example values
INSERT INTO "user" (
    "username",
    "email",
    "password_hash",
    "faculty",
    "course",
    "year"
) VALUES (
             'MaxMustermann',
             'max.mustermann@dhbw.de',
             '$2y$10$abcdefghijklmnopqrstuv',  -- gehashtes Passwort
             'T',
             'INF',
             2024
         );


-- Insert Studiengang
INSERT INTO "user" (
    "username",
    "email",
    "password_hash"
) VALUES (
             'MariaM',
             'maria@dhbw.de',
             '$2y$10$xyz123...'
         );

-- Add new private chat
INSERT INTO "chat" (
    "chat_name",
    "chat_type"
) VALUES (
             NULL, -- if chat_type is private there is no chat_name
             'personal'
         );

-- add user to globalchat (automatic when registered)
INSERT INTO "chat_participant" (
    "user_id",
    "chat_id"
) VALUES (
             1,  -- user id must be queried before
             1   -- chat id must be queried for should be queried for
         );

-- new personal chat
INSERT INTO "chat_participant" ("user_id", "chat_id") VALUES
            (1, 2),  -- user id mut be queried for
            (2, 2);  -- user id mus be queried before


INSERT INTO "chat_participant" ("user_id", "chat_id") VALUES
            (1, 5),  -- user id mut be queried for
            (2, 5),
            (3, 5),
            (4, 5);

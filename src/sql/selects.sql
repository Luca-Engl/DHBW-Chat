-- Select the last n messages 
SELECT
    m."id",
    m."content",
    m."sent_at",
    m."sender_id",
    u."username" AS sender_name,
    u."avatar_path" AS sender_avatar,
    CASE WHEN m."sender_id" = ? THEN TRUE ELSE FALSE END AS is_own
FROM "message" m
         JOIN "user" u ON m."sender_id" = u."id"
WHERE m."chat_id" = ?
ORDER BY m."sent_at" DESC
    LIMIT 50; -- n  optional can be altered
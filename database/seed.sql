-- Seed data for SDASFC
USE sdasfc;

-- Default admin login: username = admin, password = admin123
-- Change this password after first login.
-- Default security question seeded so the forgot-password flow is testable
-- immediately: "What is your mother's maiden name?" -> answer "smith"
-- Change the answer after first login via Manage Profile.
INSERT INTO admins (username, password_hash, full_name, security_question, security_answer_hash)
VALUES (
    'admin',
    '$2y$10$Nc3eRulCTYzbCkKO8FDMJ.kC7ku2D2Q1Avtmo2PIeRDjaTuLUcpCS',
    'System Administrator',
    'What is your mother''s maiden name?',
    '$2y$10$gjv6sWwKwel1SO3YShsQdepDAnb1FJubOtf6PiQBqm3O3ze19j.lS'
)
ON DUPLICATE KEY UPDATE username = username;

-- Seed Emergency Master Key Card (Brownout / Offline Hardware Bypass)
INSERT INTO users (full_name, id_number, rfid_uid, role, status)
VALUES ('Master Emergency Key (Admin)', 'MASTER-001', '93 39 6E 1B', 'faculty', 'active')
ON DUPLICATE KEY UPDATE rfid_uid = VALUES(rfid_uid);

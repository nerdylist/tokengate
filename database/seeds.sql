-- RentPeople.io Seed Data
-- Sample data for testing and development

-- Insert Admin User
-- Password: admin123 (hashed with password_hash using PASSWORD_DEFAULT)
INSERT INTO users (email, password_hash, name, is_admin) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 1);

-- Insert Regular Users
INSERT INTO users (email, password_hash, name, is_admin) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 0),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 0),
('bob.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 0),
('alice.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Johnson', 0),
('charlie.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlie Brown', 0),
('diana.prince@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diana Prince', 0),
('eric.taylor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eric Taylor', 0),
('fiona.adams@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fiona Adams', 0),
('george.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'George Martin', 0),
('helen.carter@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Helen Carter', 0),
('ian.clarke@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ian Clarke', 0),
('julia.roberts@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Julia Roberts', 0),
('kevin.hart@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kevin Hart', 0),
('laura.palmer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Laura Palmer', 0),
('mike.ross@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Ross', 0);

-- Insert Categories
INSERT INTO categories (name, slug, description) VALUES
('Development', 'development', 'Software development and programming tasks'),
('Design', 'design', 'UI/UX design, graphics, and visual content'),
('Marketing', 'marketing', 'Marketing, advertising, and promotional work'),
('Writing', 'writing', 'Content writing, copywriting, and documentation'),
('Support', 'support', 'Customer support and technical assistance');

-- Insert Skills
INSERT INTO skills (name, slug, category_id) VALUES
('PHP', 'php', 1),
('JavaScript', 'javascript', 1),
('Python', 'python', 1),
('React', 'react', 1),
('Node.js', 'nodejs', 1),
('CSS', 'css', 1),
('UI Design', 'ui-design', 2),
('UX Research', 'ux-research', 2),
('Graphic Design', 'graphic-design', 2),
('SEO', 'seo', 3),
('Social Media', 'social-media', 3),
('Content Strategy', 'content-strategy', 3),
('Technical Writing', 'technical-writing', 4),
('Copywriting', 'copywriting', 4),
('Customer Service', 'customer-service', 5);

-- Insert Bounties
INSERT INTO bounties (user_id, category_id, title, description, budget_min, budget_max, deadline, status) VALUES
(2, 1, 'Build a REST API for E-commerce Platform', 'Need a developer to create a RESTful API for our e-commerce platform. Must handle products, orders, and user authentication.', 2000.00, 3500.00, '2026-03-15', 'open'),
(3, 2, 'Redesign Mobile App UI', 'Looking for a UI designer to refresh our mobile app interface. Modern, clean design preferred.', 1500.00, 2500.00, '2026-03-01', 'open'),
(4, 1, 'Fix JavaScript Performance Issues', 'Our web app has performance problems. Need an expert to optimize JavaScript code and improve load times.', 800.00, 1200.00, '2026-02-20', 'open'),
(5, 3, 'SEO Optimization Campaign', 'Improve our website SEO rankings. Looking for someone with proven track record in SEO.', 1000.00, 2000.00, '2026-04-01', 'open'),
(6, 4, 'Write Technical Documentation', 'Need comprehensive documentation for our API. Must be clear and well-structured.', 600.00, 1000.00, '2026-02-28', 'open'),
(7, 1, 'Develop Python Data Processing Script', 'Create a Python script to process CSV files and generate reports. Should handle large datasets.', 500.00, 800.00, '2026-03-10', 'open'),
(8, 2, 'Create Logo and Brand Identity', 'Startup needs a professional logo and complete brand identity package.', 1200.00, 2000.00, '2026-03-20', 'open'),
(9, 3, 'Manage Social Media Accounts', 'Looking for someone to manage our social media presence for 3 months. Must create engaging content.', 1500.00, 2500.00, '2026-05-01', 'open'),
(10, 5, 'Provide Customer Support Training', 'Train our support team on best practices. Should include documentation and workshop.', 800.00, 1500.00, '2026-03-05', 'open'),
(11, 1, 'Build React Dashboard', 'Create an admin dashboard using React. Should include charts, tables, and user management.', 2500.00, 4000.00, '2026-04-15', 'open');

-- Insert Bounty Skills relationships
INSERT INTO bounty_skills (bounty_id, skill_id) VALUES
(1, 1), (1, 2),
(2, 7), (2, 8),
(3, 2), (3, 6),
(4, 10),
(5, 13), (5, 14),
(6, 3),
(7, 9),
(8, 11), (8, 12),
(9, 15),
(10, 4), (10, 2);

-- Insert Profiles
INSERT INTO profiles (user_id, profile_id, bio, hourly_rate, available) VALUES
(2, 'JOH-1847', 'Full-stack developer with 5 years experience. Specialized in PHP and JavaScript.', 75.00, 1),
(3, 'JAN-2934', 'UI/UX designer passionate about creating beautiful user experiences.', 65.00, 1),
(4, 'BOB-4721', 'Performance optimization expert. Love making websites blazing fast.', 80.00, 1),
(5, 'ALI-5612', 'SEO specialist with proven results. Helped 50+ businesses rank higher.', 60.00, 1),
(6, 'CHA-6853', 'Technical writer with background in software development.', 55.00, 1),
(7, 'DIA-7294', 'Python developer and data scientist. Expert in data processing.', 70.00, 1),
(8, 'ERI-8435', 'Brand identity designer. Created logos for 100+ companies.', 85.00, 1),
(9, 'FIO-9176', 'Social media manager with creative content strategy skills.', 50.00, 1),
(10, 'GEO-1527', 'Customer service trainer with 10 years experience.', 65.00, 1),
(11, 'HEL-2668', 'React developer. Built dashboards for Fortune 500 companies.', 90.00, 1),
(12, 'IAN-3809', 'Full-stack JavaScript developer. Node.js and React expert.', 75.00, 1),
(13, 'JUL-4941', 'Graphic designer specializing in modern minimalist design.', 60.00, 1),
(14, 'KEV-5182', 'Copywriter with background in tech marketing.', 55.00, 1),
(15, 'LAU-6323', 'Web developer focused on performance and accessibility.', 70.00, 1),
(16, 'MIK-7464', 'UI designer with strong CSS and animation skills.', 65.00, 1);

-- Insert Profile Skills relationships
INSERT INTO profile_skills (profile_id, skill_id, proficiency_level) VALUES
(1, 1, 'expert'), (1, 2, 'expert'), (1, 6, 'intermediate'),
(2, 7, 'expert'), (2, 8, 'expert'), (2, 9, 'intermediate'),
(3, 2, 'expert'), (3, 6, 'expert'),
(4, 10, 'expert'), (4, 12, 'intermediate'),
(5, 13, 'expert'), (5, 14, 'intermediate'),
(6, 3, 'expert'), (6, 1, 'intermediate'),
(7, 9, 'expert'), (7, 7, 'intermediate'),
(8, 11, 'expert'), (8, 12, 'expert'),
(9, 15, 'expert'),
(10, 4, 'expert'), (10, 2, 'expert'),
(11, 2, 'expert'), (11, 5, 'expert'),
(12, 9, 'intermediate'), (12, 7, 'expert'),
(13, 14, 'expert'), (13, 12, 'intermediate'),
(14, 2, 'intermediate'), (14, 6, 'expert'),
(15, 7, 'expert'), (15, 6, 'expert');

-- Insert Applications
INSERT INTO applications (bounty_id, profile_id, cover_letter, proposed_rate, status) VALUES
(1, 1, 'I have extensive experience building REST APIs with PHP. I can deliver a secure and scalable solution.', 3000.00, 'pending'),
(2, 2, 'Your mobile app redesign project is perfect for me. I specialize in modern UI design and have worked on similar projects.', 2000.00, 'pending'),
(3, 3, 'I am an expert in JavaScript performance optimization. I can identify bottlenecks and improve your app speed significantly.', 1000.00, 'pending'),
(4, 4, 'With over 50 successful SEO campaigns, I can help improve your rankings. Let me show you my portfolio.', 1500.00, 'accepted'),
(10, 10, 'I have built numerous React dashboards and can create exactly what you need. Check out my previous work.', 3500.00, 'pending');

-- Insert Votes
INSERT INTO votes (bounty_id, profile_id, voter_user_id) VALUES
(1, 1, 3),
(1, 1, 4),
(2, 2, 2),
(3, 3, 5),
(4, 4, 6);

-- ============================================================
-- FINAL INSTALL.SQL (STRUCTURE + SAMPLE DATA)
-- Learning Management System (LMS)
-- ============================================================

CREATE DATABASE IF NOT EXISTS projek_paw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projek_paw;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','instructor','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: instructors_profile
-- ============================================================
CREATE TABLE instructors_profile (
  instructor_id INT PRIMARY KEY,
  bio TEXT,
  photo_url VARCHAR(255),
  linkedin VARCHAR(255),
  website VARCHAR(255),
  FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: courses
-- ============================================================
CREATE TABLE courses (
  course_id INT AUTO_INCREMENT PRIMARY KEY,
  instructor_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  short_desc VARCHAR(255),
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: courses_categories
-- ============================================================
CREATE TABLE courses_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  category_id INT NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: materials
-- ============================================================
CREATE TABLE materials (
  material_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  type ENUM('video','pdf','text') NOT NULL DEFAULT 'video',
  file_url VARCHAR(255),
  position INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: quizzes
-- ============================================================
CREATE TABLE quizzes (
  quiz_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: quiz_questions
-- ============================================================
CREATE TABLE quiz_questions (
  question_id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255),
  option_b VARCHAR(255),
  option_c VARCHAR(255),
  option_d VARCHAR(255),
  correct_answer CHAR(1) NOT NULL,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: enrollments
-- ============================================================
CREATE TABLE enrollments (
  enroll_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  status ENUM('active','completed') NOT NULL DEFAULT 'active',
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enroll (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  enroll_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('bank','ewallet','creditcard') NOT NULL,
  status ENUM('success','failed','pending') NOT NULL DEFAULT 'pending',
  paid_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (enroll_id) REFERENCES enrollments(enroll_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: payment_history
-- ============================================================
CREATE TABLE payment_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payment_id INT NOT NULL,
  user_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: quiz_answers
-- ============================================================
CREATE TABLE quiz_answers (
  answer_id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  user_id INT NOT NULL,
  user_answer CHAR(1),
  is_correct TINYINT(1) DEFAULT 0,
  answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(question_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: materials_progress
-- ============================================================
CREATE TABLE materials_progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  material_id INT NOT NULL,
  is_completed TINYINT(1) DEFAULT 0,
  completed_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: reviews
-- ============================================================
CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  user_id INT NOT NULL,
  rating INT,
  comment TEXT,
  reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: wishlist
-- ============================================================
CREATE TABLE wishlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  UNIQUE KEY uq_wishlist (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE announcements (
  announcement_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  instructor_id INT NOT NULL,
  title VARCHAR(200),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
  FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: certificates
-- ============================================================
CREATE TABLE certificates (
  certificate_id INT AUTO_INCREMENT PRIMARY KEY,
  enroll_id INT NOT NULL,
  issued_date DATE NOT NULL,
  cert_code VARCHAR(50) NOT NULL UNIQUE,
  FOREIGN KEY (enroll_id) REFERENCES enrollments(enroll_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- INSERT DATA: USERS
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('Admin Utama', 'admin@example.com', '$2y$10$ejWFHOS5k30lmkDDGdpFjOG4tRBBqBfWJk9YxP8GJujjaRX9IjE5.', 'admin'),
('Instruktur A', 'instructor@example.com', '$2y$10$psD3ziv32DDoKpEVK5n9luJK6lxJPF5nGJZXC4P4JGJ3FAnnfX3qW', 'instructor'),
('Mahasiswa Satu', 'student1@example.com', '$2y$10$KTTSSVbzSx3usr5wb6oF..d1ERwLLJfTnwAQM7WcDzvtoG9xZnC3e', 'student'),
('Mahasiswa Dua', 'student2@example.com', '$2y$10$eh/xmWrKigwbRH9Yz0POveilJ1fIOCl8lhxGWoBkHp7zyY9SLpM0O', 'student');

-- ============================================================
-- INSERT DATA: INSTRUCTOR PROFILE
-- ============================================================
INSERT INTO instructors_profile (instructor_id, bio, photo_url, linkedin, website) VALUES
(2, 'Instruktur berpengalaman dalam Web Development dan Database.', '', 'https://linkedin.com/in/instrukturA', 'https://instruktora.example.com');

-- ============================================================
-- INSERT DATA: CATEGORIES
-- ============================================================
INSERT INTO categories (name, description) VALUES
('Web Development', 'Belajar membuat website dari pemula sampai mahir'),
('Programming', 'Bahasa pemrograman dasar hingga lanjutan'),
('Database', 'Manajemen database SQL dan NoSQL'),
('Mobile Development', 'Pengembangan aplikasi Android dan iOS'),
('Design', 'UI/UX, desain grafis, dan prototyping');

-- ============================================================
-- INSERT DATA: COURSES
-- ============================================================
INSERT INTO courses (instructor_id, title, short_desc, description, price, level) VALUES
(2, 'Belajar HTML Dasar', 'Dasar membangun halaman web', 'HTML lengkap dengan contoh nyata', 0, 'beginner'),
(2, 'CSS Fundamental', 'Styling website modern', 'CSS responsive, layouting, dan animasi', 0, 'beginner'),
(2, 'JavaScript Modern ES6+', 'JavaScript generasi baru', 'DOM, Event, ES6+, modul, fetch API', 150000, 'intermediate'),
(2, 'PHP dan MySQL Lengkap', 'Backend Web Development', 'CRUD, autentikasi, dan OOP PHP', 120000, 'beginner'),
(2, 'Laravel Framework', 'Laravel dari dasar hingga mahir', 'Routing, Blade, ORM, API, deployment', 250000, 'advanced'),
(2, 'React JS Pemula', 'Frontend modern', 'Hooks, component, routing, state management', 180000, 'intermediate'),
(2, 'Python Dasar', 'Pemrograman Python', 'Syntax, OOP, mini projects', 0, 'beginner'),
(2, 'Data Science Python', 'Analisis data', 'Pandas, Numpy, ML dasar', 280000, 'advanced'),
(2, 'UI/UX Design', 'Dasar desain aplikasi', 'Wireframe, prototype, design thinking', 0, 'beginner'),
(2, 'Android Kotlin', 'Aplikasi Android modern', 'Kotlin, activity, API, database', 200000, 'intermediate');

-- ============================================================
-- INSERT DATA: COURSES_CATEGORIES
-- ============================================================
INSERT INTO courses_categories (course_id, category_id) VALUES
(1,1), (1,2),
(2,1),
(3,1), (3,2),
(4,1), (4,3),
(5,1),
(6,1),
(7,2),
(8,2), (8,3),
(9,5),
(10,4);

-- ============================================================
-- INSERT DATA: MATERIALS
-- ============================================================
INSERT INTO materials (course_id, title, type, file_url, position) VALUES
(1, 'Pengenalan HTML', 'text', 'materi/html_intro.html', 1),
(1, 'Struktur Dokumen HTML', 'pdf', 'materi/html_structure.pdf', 2),

(2, 'Dasar CSS', 'text', 'materi/css_basic.html', 1),
(2, 'Flexbox dan Grid', 'video', 'https://example.com/css_flexbox.mp4', 2),

(3, 'JavaScript Dasar ES6+', 'video', 'https://example.com/js_intro.mp4', 1),

(4, 'Dasar PHP', 'text', 'materi/php_basic.html', 1),
(4, 'Koneksi Database MySQL', 'video', 'https://example.com/php_mysql.mp4', 2),

(5, 'Instalasi Laravel', 'video', 'https://example.com/laravel_install.mp4', 1),
(5, 'Routing dan Blade', 'text', 'materi/laravel_routing.html', 2);

-- ============================================================
-- INSERT DATA: QUIZZES
-- ============================================================
INSERT INTO quizzes (course_id, title) VALUES
(1, 'Kuis HTML Dasar'),
(3, 'Kuis JavaScript ES6'),
(4, 'Kuis PHP MySQL'),
(5, 'Kuis Laravel Dasar');

-- ============================================================
-- INSERT DATA: QUIZ QUESTIONS
-- ============================================================
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES
(1, 'Tag pembuka HTML adalah?', '<html>', '<body>', '<div>', '<head>', 'A'),
(1, 'Elemen teks tebal adalah?', '<b>', '<strong>', '<bold>', '<text>', 'B'),

(2, 'Keyword ES6 untuk variabel?', 'var', 'let', 'const', 'define', 'B'),
(2, 'Jenis fungsi ES6 modern?', 'arrow function', 'regular function', 'prototype', 'async', 'A'),

(3, 'Sintaks output PHP?', 'echo', 'println', 'output()', 'say()', 'A'),
(3, 'Koneksi database PHP?', 'mysqli_connect', 'sql_connect', 'db_open', 'db_connect', 'A'),

(4, 'Perintah membuat project Laravel?', 'laravel new', 'composer create-project', 'php install laravel', 'php artisan new', 'A');

-- ============================================================
-- END OF FILE
-- ============================================================

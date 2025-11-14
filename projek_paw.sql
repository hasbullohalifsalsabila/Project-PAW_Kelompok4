-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 07:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projek_paw`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Web Development', 'Belajar membuat website dari pemula sampai mahir'),
(2, 'Programming', 'Bahasa pemrograman dasar hingga lanjutan'),
(3, 'Database', 'Manajemen database SQL dan NoSQL'),
(4, 'Mobile Development', 'Pengembangan aplikasi Android dan iOS'),
(5, 'Design', 'UI/UX, desain grafis, dan prototyping');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `enroll_id` int(11) NOT NULL,
  `issued_date` date NOT NULL,
  `cert_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `enroll_id`, `issued_date`, `cert_code`) VALUES
(1, 3, '2025-11-15', 'CERT-623B028F');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `instructor_id`, `title`, `short_desc`, `description`, `price`, `level`, `created_at`) VALUES
(1, 2, 'Belajar HTML Dasar', 'Dasar membangun halaman web', 'HTML lengkap dengan contoh nyata', 0.00, 'beginner', '2025-11-14 18:09:20'),
(2, 2, 'CSS Fundamental', 'Styling website modern', 'CSS responsive, layouting, dan animasi', 0.00, 'beginner', '2025-11-14 18:09:20'),
(3, 2, 'JavaScript Modern ES6+', 'JavaScript generasi baru', 'DOM, Event, ES6+, modul, fetch API', 150000.00, 'intermediate', '2025-11-14 18:09:20'),
(4, 2, 'PHP dan MySQL Lengkap', 'Backend Web Development', 'CRUD, autentikasi, dan OOP PHP', 120000.00, 'beginner', '2025-11-14 18:09:20'),
(5, 2, 'Laravel Framework', 'Laravel dari dasar hingga mahir', 'Routing, Blade, ORM, API, deployment', 250000.00, 'advanced', '2025-11-14 18:09:20'),
(6, 2, 'React JS Pemula', 'Frontend modern', 'Hooks, component, routing, state management', 180000.00, 'intermediate', '2025-11-14 18:09:20'),
(7, 2, 'Python Dasar', 'Pemrograman Python', 'Syntax, OOP, mini projects', 0.00, 'beginner', '2025-11-14 18:09:20'),
(8, 2, 'Data Science Python', 'Analisis data', 'Pandas, Numpy, ML dasar', 280000.00, 'advanced', '2025-11-14 18:09:20'),
(9, 2, 'UI/UX Design', 'Dasar desain aplikasi', 'Wireframe, prototype, design thinking', 0.00, 'beginner', '2025-11-14 18:09:20'),
(10, 2, 'Android Kotlin', 'Aplikasi Android modern', 'Kotlin, activity, API, database', 200000.00, 'intermediate', '2025-11-14 18:09:20');

-- --------------------------------------------------------

--
-- Table structure for table `courses_categories`
--

CREATE TABLE `courses_categories` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses_categories`
--

INSERT INTO `courses_categories` (`id`, `course_id`, `category_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(4, 3, 1),
(5, 3, 2),
(6, 4, 1),
(7, 4, 3),
(8, 5, 1),
(9, 6, 1),
(10, 7, 2),
(11, 8, 2),
(12, 8, 3),
(13, 9, 5),
(14, 10, 4);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enroll_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enroll_id`, `user_id`, `course_id`, `status`, `enrolled_at`) VALUES
(2, 6, 3, 'active', '2025-11-14 18:32:47'),
(3, 6, 1, 'completed', '2025-11-14 18:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `instructors_profile`
--

CREATE TABLE `instructors_profile` (
  `instructor_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instructors_profile`
--

INSERT INTO `instructors_profile` (`instructor_id`, `bio`, `photo_url`, `linkedin`, `website`) VALUES
(2, 'Instruktur berpengalaman dalam Web Development dan Database.', '', 'https://linkedin.com/in/instrukturA', 'https://instruktora.example.com');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `type` enum('video','pdf','text') NOT NULL DEFAULT 'video',
  `file_url` varchar(255) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`material_id`, `course_id`, `title`, `type`, `file_url`, `position`, `created_at`) VALUES
(1, 1, 'Pengenalan HTML', 'text', 'materi/html_intro.html', 1, '2025-11-14 18:09:20'),
(2, 1, 'Struktur Dokumen HTML', 'pdf', 'materi/html_structure.pdf', 2, '2025-11-14 18:09:20'),
(3, 2, 'Dasar CSS', 'text', 'materi/css_basic.html', 1, '2025-11-14 18:09:20'),
(4, 2, 'Flexbox dan Grid', 'video', 'https://example.com/css_flexbox.mp4', 2, '2025-11-14 18:09:20'),
(5, 3, 'JavaScript Dasar ES6+', 'video', 'https://example.com/js_intro.mp4', 1, '2025-11-14 18:09:20'),
(6, 4, 'Dasar PHP', 'text', 'materi/php_basic.html', 1, '2025-11-14 18:09:20'),
(7, 4, 'Koneksi Database MySQL', 'video', 'https://example.com/php_mysql.mp4', 2, '2025-11-14 18:09:20'),
(8, 5, 'Instalasi Laravel', 'video', 'https://example.com/laravel_install.mp4', 1, '2025-11-14 18:09:20'),
(9, 5, 'Routing dan Blade', 'text', 'materi/laravel_routing.html', 2, '2025-11-14 18:09:20');

-- --------------------------------------------------------

--
-- Table structure for table `materials_progress`
--

CREATE TABLE `materials_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `enroll_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('bank','ewallet','creditcard') NOT NULL,
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `enroll_id`, `amount`, `method`, `status`, `paid_at`) VALUES
(1, 2, 150000.00, 'ewallet', 'success', '2025-11-14 18:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `payment_id`, `user_id`, `action`, `timestamp`) VALUES
(1, 1, 6, 'paid', '2025-11-14 18:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `quiz_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`quiz_id`, `course_id`, `title`) VALUES
(1, 1, 'Kuis HTML Dasar'),
(2, 3, 'Kuis JavaScript ES6'),
(3, 4, 'Kuis PHP MySQL'),
(4, 5, 'Kuis Laravel Dasar');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_answer` char(1) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`answer_id`, `question_id`, `user_id`, `user_answer`, `is_correct`, `answered_at`) VALUES
(1, 1, 6, 'A', 1, '2025-11-14 18:34:40'),
(2, 2, 6, 'C', 0, '2025-11-14 18:34:40'),
(3, 1, 6, 'A', 1, '2025-11-14 18:34:50'),
(4, 2, 6, 'A', 0, '2025-11-14 18:34:50'),
(5, 1, 6, 'A', 1, '2025-11-14 18:34:58'),
(6, 2, 6, 'B', 1, '2025-11-14 18:34:58');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`question_id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(1, 1, 'Tag pembuka HTML adalah?', '<html>', '<body>', '<div>', '<head>', 'A'),
(2, 1, 'Elemen teks tebal adalah?', '<b>', '<strong>', '<bold>', '<text>', 'B'),
(3, 2, 'Keyword ES6 untuk variabel?', 'var', 'let', 'const', 'define', 'B'),
(4, 2, 'Jenis fungsi ES6 modern?', 'arrow function', 'regular function', 'prototype', 'async', 'A'),
(5, 3, 'Sintaks output PHP?', 'echo', 'println', 'output()', 'say()', 'A'),
(6, 3, 'Koneksi database PHP?', 'mysqli_connect', 'sql_connect', 'db_open', 'db_connect', 'A'),
(7, 4, 'Perintah membuat project Laravel?', 'laravel new', 'composer create-project', 'php install laravel', 'php artisan new', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `reviewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','instructor','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Utama', 'admin@example.com', '$2y$10$ejWFHOS5k30lmkDDGdpFjOG4tRBBqBfWJk9YxP8GJujjaRX9IjE5.', 'admin', '2025-11-14 18:09:20'),
(2, 'Instruktur A', 'instructor@example.com', '$2y$10$psD3ziv32DDoKpEVK5n9luJK6lxJPF5nGJZXC4P4JGJ3FAnnfX3qW', 'instructor', '2025-11-14 18:09:20'),
(3, 'Mahasiswa Satu', 'student1@example.com', '$2y$10$KTTSSVbzSx3usr5wb6oF..d1ERwLLJfTnwAQM7WcDzvtoG9xZnC3e', 'student', '2025-11-14 18:09:20'),
(4, 'Mahasiswa Dua', 'student2@example.com', '$2y$10$eh/xmWrKigwbRH9Yz0POveilJ1fIOCl8lhxGWoBkHp7zyY9SLpM0O', 'student', '2025-11-14 18:09:20'),
(6, 'uqi', 'uqi@gmail.com', '$2y$10$hrVCXDs7e5vIlqaeoXvT0.B09B16qqD5n00VCo79JwB3lCXFcNtvu', 'student', '2025-11-14 18:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `cert_code` (`cert_code`),
  ADD KEY `enroll_id` (`enroll_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `courses_categories`
--
ALTER TABLE `courses_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enroll_id`),
  ADD UNIQUE KEY `uq_enroll` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `instructors_profile`
--
ALTER TABLE `instructors_profile`
  ADD PRIMARY KEY (`instructor_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `materials_progress`
--
ALTER TABLE `materials_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `enroll_id` (`enroll_id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wishlist` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `courses_categories`
--
ALTER TABLE `courses_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `materials_progress`
--
ALTER TABLE `materials_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`enroll_id`) REFERENCES `enrollments` (`enroll_id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `courses_categories`
--
ALTER TABLE `courses_categories`
  ADD CONSTRAINT `courses_categories_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructors_profile`
--
ALTER TABLE `instructors_profile`
  ADD CONSTRAINT `instructors_profile_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `materials_progress`
--
ALTER TABLE `materials_progress`
  ADD CONSTRAINT `materials_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_progress_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`enroll_id`) REFERENCES `enrollments` (`enroll_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_answers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

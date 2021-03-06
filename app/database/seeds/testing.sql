USE `courseadvisor`;

INSERT INTO `students` (`id`, `sciper`, `email`, `firstname`, `lastname`, `semester`, `is_admin`, `section_id`, `created_at`, `updated_at`) VALUES
(1, '115687', 'john.snow@thewa.cz', 'John', 'Snow', 'BA6', 1, 6, '2015-11-11 22:29:10', '2015-11-11 22:29:10'),
(2, '325471', 'cersei.lannister@epfl.ch', 'Cersei', 'Lannister', 'E', 0, 4, '2015-11-11 22:42:02', '2015-11-11 22:42:02');

INSERT INTO `student_study_plan` (`study_plan_id`, `student_id`) VALUES
(19, 1);

INSERT INTO `comments` (`id`, `review_id`, `parent_id`, `student_id`, `body`, `score`, `created_at`, `updated_at`) VALUES
(4, 1, NULL, 2, 'Hi, I want to know if this course is worth its credits.', 0, '2015-11-11 22:42:25', '2015-11-11 22:42:25'),
(5, 1, 4, 1, 'Yeah, totally worth it', 1, '2015-11-11 22:42:56', '2015-11-11 22:43:13'),
(6, 2, NULL, 1, 'Did you take some fries with your bacon avocado?', 0, '2015-11-13 20:40:22', '2015-11-13 20:40:22');

INSERT INTO `reviews` (`id`, `course_id`, `student_id`, `title`, `lectures_grade`, `exercises_grade`, `content_grade`, `avg_grade`, `difficulty`, `comment`, `is_anonymous`, `status`, `created_at`, `updated_at`, `score`) VALUES
(1, 921, 1, 'Still more interesting than a game of bridge', 3, 1, 3, 2.3333333333333, 2, 'This course bridges the gap between plans and their concrete implementation.', '0', 'published', '2015-11-11 22:34:03', '2015-11-11 22:43:14', 0),
(2, 899, 2, 'Good overall', 3, 5, 4, 4, 2, 'You get to eat at Holly Cow which is nice', '0', 'published', '2015-11-13 13:37:00', '2015-11-13 13:37:00', 0);

INSERT INTO `inscriptions` (`course_id`, `year`, `term`, `sciper`) VALUES
-- Makes sure test subject is subscribed to a course
(101, YEAR(CURDATE()), 'spring', 115687);

-- course:
-- - name
-- - string_id
-- - grade_avg (cached average)
--
--     /\
--     ||
--
-- course_inst:                       review:
-- - year                             - grade
-- - term                        <==  - course_inst_id
-- - credits
-- - teacher

-- ------------------ --
-- Course refatoring  --
-- ------------------ --

-- New structures

CREATE TABLE IF NOT EXISTS `course_instances` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `credits` tinyint(4) NOT NULL,
  `year` year(4) NOT NULL,
  `term` enum('SPRING','FALL') NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `course_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_course_inst_teacher_id` (`teacher_id`),
  ADD KEY `fk_course_inst_course_id` (`course_id`);

ALTER TABLE `course_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


-- Migrate existing data

-- This constraint helps with migrating reviews
INSERT INTO `course_instances` (`teacher_id`, `credits`, `year`, `term`, `course_id`)
SELECT `teacher_id`, '0', '2015', 'SPRING', `id`
FROM courses
ORDER BY `id`;


-- Remove old structure

ALTER TABLE `courses` DROP COLUMN teacher_id;


-- ------------------- --
-- Refactoring reviews --
-- ------------------- --

ALTER TABLE `reviews`
DROP KEY `fk_reviews_courses2_idx`,
DROP KEY `course_student_unicity`;

-- We will "blindly" translate review's course_id into course_instance_id so we assume they are the same at that point.
SELECT @value := count(*) FROM `course_instances` WHERE `id` != `course_id`;
SET SESSION sql_mode = if(@value='0', @@SESSION.sql_mode, 'Assertion failed: instance ids must match course ids!');

ALTER TABLE `reviews` CHANGE COLUMN `course_id` `course_instance_id` INT(11) NOT NULL;

ALTER TABLE `reviews`
ADD KEY `fk_reviews_course_instance` (`course_instance_id`),
ADD UNIQUE KEY `course_student_unicity` (`course_instance_id`,`student_id`);

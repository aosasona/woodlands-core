# Entities

This document lists all the entities and their appropriate role(s) in the system

- `user`: A general abstraction for someone using any of the systems, this makes it easier to generalise lookups for and during authentication and authorization.
- `staff`: This entity represents both teaching and non-teaching staff members.
- `student`: As the name implies, someone who is not an admin but only attends the school as a student.
- `student_tutor`: This describes the relationship between a staff and a student, every student *must* have a tutor.
- `course`: Any course offered by the University, an umbrella entity representing the chosen path of study (e.g. Computer Science) 
- `student_course`: This describes the relationship between a student and the available courses, it acts as a list of mapping for what students are enrolled on what courses. A student can only be enrolled on ONE course in this University.
- `module`: A child of a course, representing individual areas of study under a particular course or in some cases, courses; otherwise known as subjects. For example, Web programming is a module in the `Software Engineering` course and also in the `Computer Science` course.
- `module_tutor`: Represents a staff that takes that particular module, multiple staff members can teach a single module.
- `course_module`: This entity represents what modules are available under what courses.
- `student_module`: A mapping between modules and students, it answers the question; "Which students are enrolled on this module?" and "What modules is this student enrolled on?"
- `module_assessment`: A list of assessments (e.g. TCAs) available under a module.
- `student_module_session_mapping`: It describes what students are assigned to what module sessions.
- `module_session`: This represents what periods a module runs, it also contains the expected count of weeks and attendance it is supposed to run for which is used to determine overall attendance.
- `classroom`: A classroom, as the name implies, represents what room in the school a session runs in.
- `session_attendance`: Contains records for the status of students' attendance for their assigned module sessions. 
# Relationships

This document breaks down and explains the relationships between the entities in the database.

## Users
> [!NOTE]
> A user account (or more appropriately, a user) is the general "abstraction" used to represent someone who can use any part of the system without needing to necessarily check either tables for the authentication data
- A staff has *ONE* user account and a student also has *ONE* user account

## Tutors
- A staff _may_ be a personal tutor to *MANY* students
- A student has only *ONE* personal tutor
- A staff _may_ teach *MANY* modules
- A module has *ONE OR MORE* tutors

## Courses
- A student is enrolled on only *ONE* course
- A course has *MANY* students enrolled on it
- A course has *MANY* modules attached to it

## Modules
- A student has *MANY* modules to take
- A module has *MANY* students enrolled
- A module has *MANY* assessments (e.g. TCA 1 and TCA 2)
- A module has *ONE OR MORE* sessions (e.g. every Thursday at 09:00 and Fridays at 15:00)
- A module _may_ belong to *MORE THAN ONE* courses (they will also share sessions)

## Sessions
- A session has only *ONE* classroom attached (i.e no online sessions, the school is fully physical)
- A sessions has *ONE OR MORE* students
- A student belongs to *MANY* sessions (e.g. one session for module A and two for module B; every week)
- A session (and a student mapping) has *MANY* attendance records
- A session *HAS MANY* attendance data records for each week it recurs

## Departments
- A student belongs to ONE department
- A staff belongs to ONE department and may be the head of the department
- A course belongs to ONE department

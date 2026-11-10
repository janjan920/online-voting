ONLINE VOTING SYSTEM (PHP + MySQL + XAMPP)

FEATURES
- Two roles: Administrator and Voter
- Workflow statuses: voter Pending/Approved/Rejected; election Draft/Open/Closed
- Automatic rule: a voter can submit only one vote for each position in an election
- Live results by position (refreshes every 15 seconds)
- Admin report with vote totals and percentages
- Password hashing, prepared SQL statements, session login, and CSRF tokens

REQUIREMENTS
- XAMPP with Apache and MySQL
- A modern browser

INSTALLATION
1. Extract the ZIP.
2. Copy the online_voting_system folder into C:\xampp\htdocs\
   (If your XAMPP is installed elsewhere, use its htdocs folder.)
3. Open XAMPP Control Panel. Start Apache and MySQL.
4. Visit http://localhost/phpmyadmin
5. Click Import, choose database.sql from this folder, then click Import/Go.
6. Open http://localhost/online_voting_system/setup.php
7. The setup page creates the first administrator:
      Username: admin
      Password: admin123
8. Open http://localhost/online_voting_system/
9. Log in as admin. Create an election, add candidates for each position,
   then change status from Draft to Open voting.
10. Register a voter using Create an account. Log in as admin and approve
    the voter. The voter can now log in and vote.
11. Visit Live results / Results report to see the vote totals.
12. When setup is complete, delete setup.php from the project folder.

DEMO WORKFLOW
- Register voter -> Pending
- Admin approves voter -> Approved
- Admin creates election -> Draft
- Admin adds candidates and opens election -> Open
- Voter submits one vote per position -> repeat vote is blocked
- Admin closes election -> Closed
- Results report displays vote totals and percentages

IMPORTANT NOTES
- This is a classroom/demo project, not a secure platform for public or government elections.
- Do not use it for a real election. Real elections require independent security review,
  stronger identity verification, auditability, accessibility, privacy controls, and legal compliance.
- Default XAMPP MySQL credentials are root with a blank password. If yours differ,
  edit config.php.
- The admin login is a demo credential. Do not expose this demo online.

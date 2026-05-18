# SCOTSA Platform

A PHP, MySQL, Tailwind CSS, and JavaScript platform for the School of Computing and Technology Students Association.

## Structure

- `index.php` - public homepage with hero, about, executives, announcements, gallery, and footer.
- `resources.php` - student resource portal with search and filters.
- `download.php` - secure PDF download handler with tracking.
- `contact.php` - public contact page.
- `admin/` - secure admin dashboard, login, uploads, programs, courses, announcements, and users.
- `includes/` - reusable config, database, auth, session, security, and layout files.
- `assets/css/styles.css` - custom UI styles layered on Tailwind CDN.
- `assets/js/main.js` - mobile menus and confirmation behavior.
- `database/schema.sql` - complete MySQL schema and seed data.
- `uploads/` - secure categorized PDF storage.

## Setup

1. Create the database:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. Update database credentials in `includes/config.php`.

3. Start a local PHP server from the parent folder:

   ```bash
   php -S localhost:8000
   ```

4. Visit `http://localhost:8000/scotsa-platform/index.php`.

Default admin:

- Email: `admin@scotsa.edu`
- Password: `Admin@12345`

Change the default password before production use.


# MLOOK: Document Tracking System (DTS)

A comprehensive web-based document tracking system class project that demonstrates digital document management, tracking, and routing within organizational workflows. This system showcases how traditional paper-based processing can be replaced with transparent, auditable, and efficient digital document management using DMMMSU-MLUC (Don Mariano Marcos Memorial State University - Mid La Union Campus) as an example organization.

**Project Type:** Class Project  
**Submitted by:** BSIT - 4B  
**Date:** November 2025

**Example Organization:** DMMMSU-MLUC (Don Mariano Marcos Memorial State University - Mid La Union Campus)  
**Technology Stack:** Laravel 12.0, PHP 8.2+, TailwindCSS 4.0  
**Version:** 1.0.0  
**Last Updated:** November 10, 2025

> **Note:** This is an academic class project developed to demonstrate a document tracking system. DMMMSU-MLUC is used as an example organization to showcase the system's capabilities and is not an official university system.

---

## Table of Contents
1. [I. Overview](#i-overview)
2. [II. Business Requirements](#ii-business-requirements)
3. [III. Technology Stack](#iii-technology-stack)
4. [IV. Installation Guide](#iv-installation-guide)
5. [V. User Manual](#v-user-manual)
6. [VI. System Administration](#vi-system-administration)
7. [VII. Database Schema](#vii-database-schema)
8. [VIII. Technical Documentation](#viii-technical-documentation)
9. [IX. License and Support](#ix-license-and-support)
10. [X. Features](#x-features)

---

## I. Overview

The **MLOOK Document Tracking System (DTS)** is a class project that demonstrates how to streamline document management by centralizing creation, routing, approval, and archival processes within a secure web application built on Laravel 12. The system uses DMMMSU-MLUC as an example organization, with access restricted to users with valid DMMMSU email accounts (@student.dmmmsu.edu.ph and @dmmmsu.edu.ph) to showcase authentication and authorization features.

Through DTS, users are able to:

- Create and upload digital documents with metadata
- Route documents through multi-level approval workflows
- Track document location and status in real-time
- Receive instant notifications via WebSocket technology
- Sign documents electronically with QR code verification
- Generate comprehensive analytics and reports
- Maintain complete audit trails for compliance

This solves common manual workflow issues, including:

- Lost or misplaced paper files
- Delays in routing and approval processes
- Lack of status transparency and accountability
- Difficulty retrieving archived documents
- Inefficient communication between departments
- Missing audit trails for compliance requirements

The system benefits organizations handling:

- Academic documents and administrative workflows
- Sensitive information requiring secure handling
- Multi-department approval processes
- Workflows requiring complete accountability and traceability
- High-volume document processing

By recording timestamps, actions, and maintaining version history, the system ensures traceability, compliance, and informed decision-making across the organization.

---

## II. Business Requirements

Below are the functional and non-functional requirements identified for the system.

### A. Functional Requirements
The system must provide the ability to:

- **User Management**
  - Register and authenticate securely with DMMMSU email accounts (@student.dmmmsu.edu.ph or @dmmmsu.edu.ph)
  - Email verification with 6-digit code during registration (10-minute expiration)
  - Two-factor authentication support for enhanced security
  - Manage user profiles with department and program assignments
  - Configure notification preferences
  - Track user activity and login history

- **Document Management**
  - Create documents with comprehensive metadata:
    - Title and description
    - Unique tracking number (auto-generated)
    - Document type classification
    - Urgency level (low, normal, high, urgent)
    - Deadline tracking
    - File attachments (PDF, DOC, DOCX)
  - Update document status through workflow stages:
    - Draft → Routing → Received → In Review → For Approval → Approved/Rejected → Completed → Archived
  - Digital signature capability with QR code verification
  - Tag documents for organization and filtering
  - **Comment and discussion threads** on documents with internal/public options

- **Document Routing & Tracking**
  - Forward documents to specific users or departments
  - **Auto-assignment** based on document type configuration (specific user, department, role, or routing rules)
  - Automated routing based on configurable rules
  - Track document movement and current holder
  - Return documents for revisions with remarks
  - Escalate overdue documents automatically
  - Generate unique tracking codes for external reference
  - **Admin status override** for manual workflow adjustments

- **Notifications & Alerts**
  - Real-time WebSocket notifications
  - Email notifications for document events
  - Deadline reminders and overdue alerts
  - Customizable notification preferences

- **Search & Reporting**
  - Search and filter documents by:
    - Keywords, tracking number
    - Status, urgency level
    - Date range, department
    - Tags, document type
  - Generate reports on:
    - Document flow analytics
    - Turnaround time statistics
    - Department-wise performance
    - User activity metrics
    - Completion rates over time
    - Tag usage analytics
  - Export audit logs for compliance

- **Security & Compliance**
  - Role-based access control (Admin, Registrar, Dean, Faculty, Staff)
  - Complete audit trail for all user actions
  - Data retention policies
  - Secure file storage and validation
  - Session management and security monitoring

### B. Non-Functional Requirements
The system should ensure:

- **Performance**
  - Page load times optimized with caching (1-hour cache for dashboard statistics)
  - Database query optimization with indexing and eager loading
  - Asset optimization through Vite build system
  - Support for concurrent users with queue-based processing

- **Security**
  - Encrypted password storage using Bcrypt
  - Two-factor authentication via Google Authenticator
  - CSRF protection on all forms
  - XSS and SQL injection prevention
  - Secure file upload validation
  - IP tracking and security monitoring

- **Reliability**
  - Automated backup system with Spatie Backup
  - Soft delete functionality for data recovery
  - Error logging and monitoring
  - Queue system for background processing
  - Performance metrics tracking

- **Usability**
  - Fully responsive interface accessible across all devices (mobile, tablet, desktop)
  - Mobile-first design with TailwindCSS 4.0
  - Intuitive navigation and workflow
  - Real-time updates without page refresh
  - Comprehensive dashboard with visual analytics
  - Touch-friendly interface with proper button sizing
  - Horizontal scrolling tables on mobile devices

- **Scalability**
  - Redis support for improved caching and queue performance
  - Modular service-based architecture
  - Database indexing for large datasets
  - Efficient query optimization

---

## III. Technology Stack

### Backend Technologies
- **Framework:** Laravel 12.0 (latest stable release)
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Sanctum, Google2FA (pragmarx/google2fa-laravel)
- **Email Verification:** Custom 6-digit code system with expiration
- **Real-time:** Laravel Reverb (WebSocket server)
- **Queue System:** Database/Redis with Laravel Queue
- **Cache:** Database/Redis with Laravel Cache
- **Backup:** Spatie Laravel Backup 9.3
- **Email:** Laravel Mail with SMTP support

### Frontend Technologies
- **Build Tool:** Vite 7.0
- **CSS Framework:** TailwindCSS 4.0
- **JavaScript:** Alpine.js (via Laravel Breeze patterns)
- **Real-time Updates:** Laravel Echo with Reverb WebSocket
- **Charts:** Chart.js 4.5
- **Icons:** Heroicons 2.2

### Additional Libraries
- **QR Code Generation:** Endroid QR Code 6.0, Bacon QR Code 3.0, Chillerlan PHP QR Code 5.0
- **Image Processing:** Intervention Image 3.11
- **Redis Client:** Predis 2.2

### Development Tools
- **Package Manager:** Composer 2.x, NPM
- **Code Quality:** Laravel Pint, PHPStan
- **Testing:** PHPUnit 11.5
- **Logging:** Laravel Pail 1.2

### System Requirements
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- MySQL 8.0 or higher
- Redis (optional, recommended for production)
- Web server (Apache/Nginx)

---

## IV. Installation Guide

This section provides step-by-step instructions for setting up the MLOOK Document Tracking System on your local or production environment.

### Prerequisites

Before beginning installation, ensure the following software is installed:

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- MySQL 8.0 or higher
- Git version control
- Web server (Apache or Nginx)
- Redis (optional, recommended for production)

### Step 1: Clone the Repository

Clone the project repository to your local machine:

```bash
git clone <repository-url>
cd mlook-dts-main
```

### Step 2: Install Dependencies

Install both PHP and JavaScript dependencies:

```bash
# Install PHP dependencies via Composer
composer install

# Install Node.js dependencies via NPM
npm install
```

### Step 3: Environment Configuration

Create and configure the environment file:

```bash
# Create your environment file if it doesn't exist yet
# If .env.example is unavailable, create .env manually
copy NUL .env  # Windows (PowerShell: New-Item .env -ItemType File)
# or: touch .env  # macOS/Linux

# Generate application encryption key
php artisan key:generate
```

### Step 4: Database Configuration

Edit the `.env` file and configure your database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_dts
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:

```bash
mysql -u root -p
CREATE DATABASE db_dts;
exit;
```

Run migrations to create database tables:

```bash
php artisan migrate:fresh --seed
```

This will create all necessary tables and populate them with sample data including:
- Departments and programs
- Document types with configurations
- Sample users (191+ users across all roles)
- Tags for document organization

**Note:** Use `migrate:fresh` for initial setup. For production updates, use `php artisan migrate` to preserve existing data.

### Step 5: Additional Configuration

Configure additional services in the `.env` file:

**Mail Configuration (Required for email verification):**

For Gmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Important:** For Gmail, you must use an App Password, not your regular password. Generate one at: https://myaccount.google.com/apppasswords

**WebSocket Configuration (Laravel Reverb):**
```env
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

**Cache and Queue Configuration:**
```env
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Step 6: Storage Setup

Create the symbolic link for file storage:

```bash
php artisan storage:link
```

Set appropriate permissions (Linux/Mac):

```bash
chmod -R 775 storage bootstrap/cache
```

### Step 7: Build Frontend Assets

Compile frontend assets using Vite:

```bash
# For development
npm run dev

# For production
npm run build
```

### Step 8: Running the Application

**Quick Start (Development Mode):**

Run all services concurrently with a single command:

```bash
composer dev
```

This starts:
- PHP development server (http://localhost:8000)
- Queue worker for background jobs
- Laravel Pail for log monitoring
- Vite development server for hot module replacement

**Manual Start (Alternative):**

Start each service in separate terminal windows:

```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:work

# Terminal 3: WebSocket server
php artisan reverb:start

# Terminal 4: Vite development server
npm run dev
```

### Default User Accounts

After seeding, the following accounts are available:

**Seeded Test Accounts (email patterns):**
- Admins: `admin1@dmmmsu.edu.ph`, `admin2@dmmmsu.edu.ph`
- Registrars: `registrar1@dmmmsu.edu.ph`, `registrar2@dmmmsu.edu.ph`
- Deans: `{college_code}.dean@dmmmsu.edu.ph` (e.g., `cics.dean@dmmmsu.edu.ph`)
- Department Heads: `{dept_code}.head@dmmmsu.edu.ph` (e.g., `reg.head@dmmmsu.edu.ph`)
- Faculty: `{firstname}.{lastname}{nn}@dmmmsu.edu.ph`
- Staff: `{firstname}.{lastname}{nn}@dmmmsu.edu.ph`
- Students: `{firstname}.{lastname}{nn}@student.dmmmsu.edu.ph`

Password for all seeded accounts: `password`

---

## V. User Manual

This section provides step-by-step instructions for using the MLOOK Document Tracking System. Screenshots and detailed explanations demonstrate key features and workflows.

### 1. User Registration and Authentication

**Creating an Account:**

The registration process consists of three steps:

**Step 1: Basic Information**
- Enter first name and last name
- Provide DMMMSU email address (@student.dmmmsu.edu.ph or @dmmmsu.edu.ph)
- Click "Send Verification Code"

**Step 2: Email Verification**
- Check email for 6-digit verification code
- Enter the code (valid for 10 minutes)
- Option to resend code if not received (60-second cooldown)
- Click "Verify & Continue"

**Step 3: Complete Registration**
- Enter university ID number (Student ID or Staff ID)
- Select program (for students) or department (for staff/faculty)
- Create secure password
- Confirm password
- Click "Create Account"

**Note:** For this class project demonstration, the system is configured to accept DMMMSU email accounts (@student.dmmmsu.edu.ph or @dmmmsu.edu.ph) to showcase email domain validation and verification features. Email verification is mandatory to demonstrate account authenticity controls.

**Logging In:**

Users authenticate using their registered email and password. If two-factor authentication is enabled, a verification code from Google Authenticator must be provided.

**Two-Factor Authentication Setup:**

For enhanced security, users may enable two-factor authentication:

1. Navigate to **Settings** → **Security**
2. Click **Enable Two-Factor Authentication**
3. Scan the displayed QR code with Google Authenticator app
4. Enter the six-digit verification code
5. Save the recovery codes in a secure location
6. Confirm activation

### 2. Dashboard Overview

The dashboard provides a comprehensive view of document activities:

- **Statistics Cards**: Display counts for inbox, sent, completed, and archived documents
- **Document Flow Timeline**: Visual representation of document movement over time
- **Department Statistics**: Performance metrics by department
- **User Activity**: Recent actions and engagement metrics
- **Pending Actions**: Documents requiring immediate attention
- **Completion Rate**: Trend analysis of document processing
- **Tag Analytics**: Most frequently used document tags
- **Real-time Updates**: Live notifications of document events

### 3. Creating and Uploading Documents

**To create a new document:**

1. Navigate to **Documents** → **Create New**
2. Fill in required information:
   - **Title**: Descriptive name for the document
   - **Document Type**: Select from predefined categories
   - **Description**: Detailed explanation of document purpose
   - **Urgency Level**: Choose low, normal, high, or urgent
   - **Deadline**: Set completion date (optional)
   - **Tags**: Add organizational tags for filtering
3. Upload document file (supported formats: PDF, DOC, DOCX)
4. Review information for accuracy
5. Click **Create Document**

A unique tracking number is automatically generated (format: DOC-2025-0001).

### 4. Document Routing and Forwarding

**Forwarding Documents:**

To route a document to another user or department:

1. Open the document from your inbox or documents list
2. Click the **Forward** button
3. Select recipient(s) from the user list
4. Add remarks or instructions (optional)
5. Confirm forwarding action

The document status automatically updates to "routing" and the recipient receives a notification.

**Automated Routing:**

Administrators may configure routing rules that automatically forward documents based on:
- Document type
- Originating department
- Urgency level
- Custom conditions

### 5. Tracking Documents

**Viewing Document Status:**

Users can track documents through multiple views:

- **Inbox**: Documents currently assigned to you requiring action
- **Documents**: Draft documents not yet forwarded
- **Sent**: Documents you created and forwarded to others
- **Completed**: Documents that have finished their workflow
- **Archive**: Documents marked for long-term storage

**Search and Filter:**

Documents may be located using:
- Keyword search in title or description
- Tracking number lookup
- Status filter (draft, routing, received, etc.)
- Urgency level filter
- Date range selection
- Tag filtering
- Document type classification

**Tracking History:**

Each document maintains a complete history showing:
- All users who handled the document
- Actions taken at each stage
- Timestamps for all movements
- Remarks and comments added
- Status changes throughout lifecycle

### 6. Document Actions and Workflow

**Available Actions:**

Depending on user role and document status, the following actions may be available:

- **Receive**: Acknowledge receipt of forwarded document
- **Review**: Mark document as under review
- **Approve**: Grant approval with optional remarks
- **Reject**: Deny approval with required reason
- **Return**: Send back to previous holder for revisions
- **Complete**: Mark document workflow as finished
- **Archive**: Move document to archive storage
- **Sign**: Add digital signature with QR code verification

**Approval Workflow:**

For documents requiring approval:

1. Document is forwarded to approver
2. Approver reviews document content and attachments
3. Approver selects **Approve** or **Reject**
4. Approval remarks or rejection reason must be provided
5. System records timestamp and approver identity
6. Notifications sent to relevant parties
7. Document status updates automatically

### 7. Digital Signatures

**Signing Documents:**

To electronically sign a document:

1. Open the document requiring signature
2. Click **Sign Document** button
3. System generates QR code containing:
   - Document tracking number
   - Signer identity
   - Timestamp
   - Verification hash
4. Signature is permanently recorded in audit trail

**Verifying Signatures:**

Any user may verify document signatures by scanning the QR code, which displays:
- Signer name and role
- Signature date and time
- Document verification status

### 8. Comments and Collaboration

**Adding Comments:**

Users may add comments to documents for collaboration:

1. Open the document
2. Scroll to **Comments** section
3. Type your comment or question
4. Click **Post Comment**
5. All users with access receive notification

Comments support threaded discussions and maintain chronological order.

### 9. Notifications

**Notification Types:**

Users receive notifications for:
- Documents forwarded to them
- Documents received by others
- Approval or rejection of documents
- Comments on documents they follow
- Approaching deadlines
- Overdue documents
- Status changes on documents they created

**Managing Notifications:**

1. Click notification bell icon in header
2. View unread notifications
3. Click notification to view related document
4. Mark individual notifications as read
5. Use **Mark All as Read** for bulk action

**Notification Preferences:**

Users may customize notification settings:

1. Navigate to **Profile** → **Notification Preferences**
2. Toggle email notifications on/off
3. Select which events trigger notifications
4. Set quiet hours for non-urgent notifications
5. Save preferences

### 10. Reports and Analytics

**Generating Reports:**

The system provides various analytical reports:

**Document Flow Analytics:**
- Total documents processed
- Average turnaround time
- Documents by status
- Trend analysis over time periods

**Department Performance:**
- Documents handled by department
- Average processing time per department
- Completion rates
- Bottleneck identification

**User Activity Metrics:**
- Documents created per user
- Documents forwarded per user
- Average response time
- Most active users

**Tag Analytics:**
- Most frequently used tags
- Documents per tag category
- Tag usage trends

**Accessing Reports:**

1. Navigate to **Dashboard** or **Reports** section
2. Select desired report type
3. Configure date range and filters
4. View interactive charts and graphs
5. Export data as needed

### 11. Profile and Settings Management

**Updating Profile:**

Users may modify their profile information:

1. Navigate to **Profile** page
2. Update personal information:
   - Name
   - Contact details
   - Department/program
   - Avatar image
3. Click **Save Changes**

**Changing Password:**

1. Go to **Profile** → **Security**
2. Enter current password
3. Enter new password (must meet requirements)
4. Confirm new password
5. Click **Update Password**

**Activity Log:**

Users can view their own activity history:

1. Navigate to **Profile** → **Activity**
2. Review chronological list of actions
3. Filter by date range or action type

---

## VI. System Administration

This section covers administrative functions for system managers and IT personnel.

### 1. User Management

**Creating Users:**

Administrators can manually create user accounts:

1. Navigate to **Admin** → **Users** → **Create User**
2. Enter user information (must use valid DMMMSU email: @student.dmmmsu.edu.ph or @dmmmsu.edu.ph)
3. Assign role (Admin, Registrar, Dean, Faculty, Staff)
4. Assign department and program
5. Set initial password
6. Send welcome email with login credentials

**Managing Users:**

- View list of all users with filtering options
- Edit user information and roles
- Deactivate or delete user accounts
- Reset user passwords
- View user activity logs
- Manage two-factor authentication status

### 2. Department and Program Management

**Departments:**

Administrators manage organizational departments:

1. Navigate to **Admin** → **Departments**
2. Create, edit, or delete departments
3. Assign department heads
4. Configure department-specific settings

**Programs:**

Manage academic or organizational programs:

1. Navigate to **Admin** → **Programs**
2. Associate programs with departments
3. Configure program-specific workflows

### 3. Document Type Configuration

**Managing Document Types:**

1. Navigate to **Admin** → **Document Types**
2. Create new document type categories
3. Define required fields for each type
4. Set default urgency levels
5. Configure approval requirements
6. Assign icons and colors for visual identification

### 4. Routing Rules

**Automated Routing Configuration:**

1. Navigate to **Admin** → **Routing Rules**
2. Create new routing rule
3. Define conditions:
   - Document type
   - Originating department
   - Urgency level
   - Custom criteria
4. Specify automatic recipients
5. Set rule priority
6. Enable or disable rules as needed

### 5. Tag Management

**Organizing Tags:**

1. Navigate to **Admin** → **Tags**
2. Create new tags for document organization
3. Edit existing tags
4. Merge duplicate tags
5. Delete unused tags
6. View tag usage statistics

### 6. System Settings

**Global Configuration:**

Administrators can configure system-wide settings:

- Application name and branding
- Email server configuration
- WebSocket server settings
- File upload limits and allowed types
- Session timeout duration
- Backup schedule
- Maintenance mode

### 7. Backup Management

**Manual Backups:**

Execute immediate backup:

```bash
php artisan backup:run
```

**Automated Backups:**

Configure scheduled backups via cron:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Backup Contents:**

- Complete database export
- Uploaded document files
- Configuration files
- Application logs

**Restoration:**

Follow Laravel backup restoration procedures using Spatie Backup package documentation.

### 8. Audit Logs

**Viewing Audit Trail:**

1. Navigate to **Admin** → **Audit Logs**
2. View comprehensive log of all system actions
3. Filter by:
   - User
   - Action type
   - Date range
   - Affected resource
4. Export logs for compliance reporting

**Logged Actions:**

- User authentication events
- Document creation, modification, deletion
- Document forwarding and status changes
- Approval and rejection actions
- Configuration changes
- User management actions

### 9. Performance Monitoring

**System Metrics:**

1. Navigate to **Admin** → **Performance**
2. View real-time performance data:
   - Page load times
   - Database query performance
   - Memory usage
   - Active users
   - Queue status
3. Identify performance bottlenecks
4. Review optimization recommendations

### 10. Maintenance Tasks

**Cache Management:**

Clear various caches to resolve issues:

```bash
php artisan cache:clear      # Application cache
php artisan config:clear     # Configuration cache
php artisan view:clear       # Compiled views
php artisan route:clear      # Route cache
```

**Queue Management:**

Monitor and manage background jobs:

```bash
php artisan queue:work       # Start queue worker
php artisan queue:restart    # Restart all workers
php artisan queue:failed     # View failed jobs
php artisan queue:retry all  # Retry failed jobs
```

**Database Maintenance:**

```bash
php artisan migrate                # Run pending migrations
php artisan db:seed                # Seed database
php artisan migrate:fresh --seed   # Fresh migration with seeding (caution: data loss)
php artisan migrate:rollback       # Rollback last migration batch
php artisan migrate:status         # Check migration status
```

### 11. Security Management

**Security Best Practices:**

- Regularly update Laravel and dependencies
- Monitor audit logs for suspicious activity
- Enforce strong password policies
- Enable two-factor authentication for administrators
- Restrict file upload types and sizes
- Implement IP whitelisting for admin access
- Regular security audits
- Keep backup copies off-site

### 12. Troubleshooting

**Common Issues and Solutions:**

**Queue Not Processing:**
```bash
php artisan queue:restart
php artisan queue:work
```

**WebSocket Connection Failed:**
- Verify Reverb server is running: `php artisan reverb:start`
- Check REVERB_* configuration in `.env`
- Ensure firewall allows port 8080
- Verify WebSocket URL in frontend configuration

**Email Not Sending:**
- Verify MAIL_* configuration in `.env`
- For Gmail, use App Password instead of regular password
- Check queue worker is running
- Review mail logs for errors

**Permission Errors:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Performance Issues:**
- Enable Redis for caching and queues
- Optimize database with proper indexing
- Review slow query logs
- Enable production mode caching
- Use CDN for static assets

---

## VII. Database Schema

### Migration Files Overview

The system uses **26 consolidated migration files** organized in chronological order to ensure proper dependency resolution:

#### Core System Tables (7 files)
1. **Departments** - Organizational departments
2. **Programs** - Academic/organizational programs
3. **Users** - User accounts with roles and authentication
4. **Cache** - Application caching
5. **Jobs** - Queue system for background processing
6. **Password Resets** - Password recovery tokens
7. **Notifications** - User notification storage

#### Document Management Tables (8 files)
8. **Document Types** - Document classification categories
9. **Documents** - Main document records with metadata
   - Includes: tracking numbers, status, urgency, deadlines
   - Consolidated fields: expiration dates, tags, category
10. **Document Tracking** - Document routing and movement history
11. **Document Actions** - User actions on documents
12. **Document Comments** - Discussion threads on documents
14. **Document Attachments** - File attachments to documents

#### Additional Features (12 files)
16. **Personal Access Tokens** - API authentication tokens
17. **Audit Logs** - Complete system audit trail
18. **Database Indexes** - Performance optimization indexes
19. **Document Receivers** - Multi-recipient document routing
20. **Routing Rules** - Automated routing configurations
21. **Performance Metrics** - System performance tracking
22. **Tags** - Document organization tags
23. **Permissions** - Role-based access control
24. **Data Retention Policies** - Compliance and archival rules
25. **Document Signatures** - Digital signature records
26. **Document Tag** - Pivot table for document-tag relationships
27. **Email Verifications** - Email verification codes

### Key Database Features

**Consolidated Schema:**
- All document-related fields in single migration
- No redundant modification migrations
- Clean rollback capabilities

**Performance Optimizations:**
- Strategic indexing on frequently queried columns
- Composite indexes for complex queries
- Foreign key constraints for data integrity

**Data Integrity:**
- Soft deletes for data recovery
- Foreign key cascades and nullOnDelete
- Unique constraints on critical fields

**Audit Trail:**
- Complete tracking of all user actions
- Nullable auditable_type for system-wide events
- IP address and user agent logging

---

## VIII. Technical Documentation

### API Documentation

This project primarily exposes web routes and internal dashboard APIs (e.g., `routes/web.php`, `/dashboard/api/*`) used by the application UI. There is no `/api/v1/*` namespace in this repository.

If you need programmatic access, you can:
- Use the existing dashboard endpoints under `/dashboard/api/*` where applicable; or
- Add dedicated API routes under `routes/api.php` and protect them with Sanctum.

Optional: To create a Sanctum token for custom endpoints you add:

```bash
php artisan tinker
$user = \App\Models\User::find(1);
$token = $user->createToken('api-token')->plainTextToken;
```

### Testing

**Running Tests:**

```bash
# Run all tests
composer test

# Run specific test suite
php artisan test --filter=DocumentTest

# Run with coverage report
php artisan test --coverage
```

### Code Quality

**Code Style (Laravel Pint):**

```bash
composer pint
```

**Static Analysis (optional):**

```bash
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse
```

---

## IX. License and Support

### License

This project is licensed under the MIT License (see `composer.json` license field). If a `LICENSE` file is not present, you may add the standard MIT text for clarity.

### Acknowledgments

- Built with [Laravel Framework](https://laravel.com)
- UI components from [TailwindCSS](https://tailwindcss.com)
- Icons from [Heroicons](https://heroicons.com)
- Charts powered by [Chart.js](https://www.chartjs.org)
- Backup system by [Spatie Laravel Backup](https://spatie.be/docs/laravel-backup)

### Support and Contributions

**For Issues and Questions:**
- Create an issue on the project repository
- Review existing documentation
- Contact system administrators

**Contributing:**

Contributions are welcome. Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/NewFeature`)
3. Commit changes with descriptive messages
4. Push to the branch (`git push origin feature/NewFeature`)
5. Open a Pull Request with detailed description

---

## X. Features

### Core Features

**Authentication & Security**
- 6-digit email verification during registration (10-minute expiration)
- Mandatory email verification for all new accounts
- Two-factor authentication support with Google Authenticator
- DMMMSU email domain restriction (@student.dmmmsu.edu.ph, @dmmmsu.edu.ph)
- 60-second cooldown on resend verification code
- Automatic cleanup of expired verification codes
- Role-based access control (Admin, Registrar, Dean, Faculty, Staff, Student)
- Complete audit trail for all user actions

**Document Management**
- Create documents with comprehensive metadata (title, type, urgency, deadline)
- Auto-assignment based on document type configuration
- Role-based filtering for document recipients
- Comments system with internal/public support
- Admin status override with audit trail
- Enhanced forwarding validation
- Document tracking
- Digital signatures with QR codes
- Tag-based organization and filtering
- File attachments (PDF, DOC, DOCX)

**Document Workflow**
- Multi-stage workflow: Draft → Routing → Received → In Review → For Approval → Approved/Rejected → Completed → Archived
- Forward documents to specific users or departments
- Return documents for revisions with remarks
- Escalate overdue documents automatically
- Unique tracking numbers (format: DOC-2025-0001)
- Complete document history and audit trail

**Dashboard & Analytics**
- Role-based analytics and chart visibility
- Real-time document flow timeline
- Department performance metrics
- User activity tracking
- Tag usage analytics
- Document type distribution charts
- Status and urgency level visualizations
- Weekly and monthly activity trends
- Admin-only charts (User Distribution, Popular Tags)

**Search & Filtering**
- Comprehensive status filtering across document pages
- My Documents: Filter by Draft, Returned
- Sent Documents: Filter by Routing, Received, In Review, For Approval, Returned
- Completed Documents: Filter by Completed, Approved, Rejected
- Archive: Shows archived documents only
- Search by tracking number, keywords, date range
- Filter by document type, urgency level, tags

**Real-time Broadcasting & Notifications**
- Complete real-time broadcasting system across all modules
- 29 broadcasting events for instant updates
- WebSocket notifications via Laravel Reverb
- Real-time document updates (comments, attachments, signatures, status changes)
- Live sidebar badge updates (inbox, documents, sent, completed)
- Real-time profile and user info updates
- Instant notification updates in navbar
- Live updates for tags and categories
- Real-time audit logs and backup status
- Email notifications for document events
- Deadline reminders and overdue alerts
- Customizable notification preferences
- Mark as read/unread functionality
- Cross-browser real-time synchronization

**User Interface**
- Fully responsive design for mobile, tablet, and desktop
- Mobile-first approach with TailwindCSS 4.0
- Touch-friendly interface with proper button sizing (min 44x44px)
- Responsive tables with horizontal scrolling on mobile
- Context-aware navigation with dynamic back buttons
- Consistent hover effects across all interactive elements
- Dark mode support

**Database & Performance**
- Optimized indexing without duplicates
- Proper foreign key constraints
- Soft deletes for data recovery
- Query optimization with eager loading
- 1-hour cache for dashboard statistics
- Redis support for improved performance
- Queue system for background processing
- Automated backup system

---

**Project Information:**  
**Name:** MLOOK Document Tracking System  
**Type:** Class Project  
**Version:** 1.0.0  
**Last Updated:** November 10, 2025  
**Example Organization:** DMMMSU-MLUC (Don Mariano Marcos Memorial State University - Mid La Union Campus)  
**Technology:** Laravel 12.0, PHP 8.2+, TailwindCSS 4.0  
**Submitted by:** BSIT - 4B  
**Database:** 26 optimized migration files, fully tested and consolidated

> This is an academic class project demonstrating document tracking system capabilities.

---

## End

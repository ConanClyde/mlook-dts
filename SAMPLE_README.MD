# E-Track: Document Tracking System (DTS)

A web-based platform designed to upload, send, track, and retrieve documents securely. This system replaces traditional paper-based processing with transparent, auditable, and efficient digital workflows.

**Submitted:** BSIT - 4B  
**Date:** December 2025

---

## Table of Contents
1. [I. Overview](#i-overview)
2. [II. Business Requirements](#ii-business-requirements)
3. [III. Major Interface Wireframes](#iii-major-interface-wireframes)
4. [IV. User Manual](#iv-user-manual)

---

## I. Overview

The **E-Track: Document Tracking System (DTS)** streamlines document management by centralizing upload, review, and approval processes within a secure web application.

Through DTS, users are able to:

- Upload digital documents
- Tag documents with metadata
- Track location and status
- Retrieve files easily

This solves common manual workflow issues, including:

- Lost or misplaced paper files
- Delays in routing and approval
- Lack of status transparency
- Difficulty retrieving archived documents

The system benefits organizations handling:

- Sensitive information
- Multi-level approvals
- Workflows requiring accountability

By recording timestamps and actions, the system ensures traceability and informed decision-making.

---

## II. Business Requirements

Below are the functional and non-functional requirements identified for the system.

### A. Functional Requirements
The system must provide the ability to:

- Register and log in securely
- Upload documents with metadata such as:
  - Title
  - Reference number
  - Description
- Update document status, including:
  - Received
  - In Review
  - Approved
  - Released
- Generate unique tracking codes
- Search and filter documents by:
  - Keywords
  - Status
  - Date range
- Produce audit logs for all user actions
- Generate reports on:
  - Turnaround time
  - Pending documents
  - Document flow history

### B. Non-Functional Requirements
The system should ensure:

- Page load times not exceeding three seconds under normal operation
- Secure storage of user data through encryption
- A responsive interface accessible across devices
- Role-based access control for:
  - Administrators
  - Clerks
  - Regular users
- Reliable performance with multiple concurrent users
- Regular data backup and recovery measures

---

## III. Major Interface Wireframes

Below are the screenshots for the application.

### User Area Pages

#### Figure 3.1 — Dashboard Page

#### Figure 3.2 — Upload Documents Page

#### Figure 3.3 — My Documents Page

#### Figure 3.4 — Notifications Page

#### Figure 3.5 — Profile Page


---

### Administrator Pages

---

## IV. User Manual

Provide step-by-step instructions and demonstrate with screenshots where applicable.

### 1. Registering and Logging In
Users may create an account and authenticate using valid credentials. Password requirements and optional security features must be followed.

### 2. Uploading Documents
Users can upload supported file types while supplying required metadata. A confirmation message should appear after a successful upload.

### 3. Tracking Documents
Documents can be monitored using:
- Search filters
- Tracking codes
- Status history logs

Filtering and sorting options are available to narrow down search results.

### 4. Updating Document Status (Authorized Personnel)
Administrators and clerks may:
- Change document status
- Add notes or remarks
- Attach supplemental files if needed

### 5. Viewing Notifications
Notifications inform users about:
- Status updates
- Document approvals
- Released files

These alerts appear within the interface and may be sent via email depending on system configuration.

### 6. Generating Reports
Reports include statistics such as:
- Pending items
- Flow history
- Average turnaround times

These provide insight into workflow performance.

### 7. Managing Profile and Settings
Users may update:
- Contact information
- Passwords
- Notification preferences

---

## End 

# Project Plan: Simple Testimonials Collector

## Goal
A plugin that allows users to submit testimonials via a frontend form. Admins can review and approve testimonials in the dashboard. Approved testimonials can be displayed anywhere using a shortcode.

## Architecture
- **Main File**: `simple-testimonials-collector.php`
- **Loader**: `includes/class-stc-loader.php` (Orchestrates hooks)
- **Post Types**: `includes/class-stc-post-types.php` (CPT & Meta Boxes)
- **Admin**: `includes/class-stc-admin.php` (Menus & Settings)
- **Frontend**: `includes/class-stc-frontend.php` (Shortcodes & Forms)

## Features
1. **Custom Post Type**: `testimonial`
   - Supports: Title, Editor.
   - Meta: Rating (1-5), Submitter Email.
2. **Admin Area**:
   - Menu: "Testimonials"
   - Settings: Toggle Rating field.
3. **Frontend**:
   - `[submit_testimonial]`: Form for users.
   - `[testimonials]`: List of approved testimonials.

## Development Steps
1.  **Setup**: Folder structure & main file.
2.  **Data Layer**: Register CPT and Meta Boxes.
3.  **Admin**: Add menu and settings page.
4.  **Frontend**: Build submission form and display list.
5.  **Polish**: Validation, Security, Styling.

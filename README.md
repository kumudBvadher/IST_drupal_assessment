# Mini-Task 1: Dataset Upload (Basic)

This custom Drupal module provides a simple file upload form that validates and displays basic information about the uploaded file.

## Features

- Standard Drupal form with file input (no Dropzone.js)
- Validates file extensions ('.csv', '.xlsx')
- Displays:
  - File name
  - File size (in KB)
  - File MIME type
- Includes basic 'try/catch' error handling

## How It Works

1. **Form Setup**
   - Built using Drupal Form API ('FormBase' or 'FormStateInterface')
   - File upload handled via 'file_save_upload()'

2. **Validation**
   - Checks if file is uploaded
   - Ensures extension is '.csv' or '.xlsx'
   - If invalid, shows a form error

3. **On Submit**
   - Displays file metadata (name, size, MIME type)
   - All logic wrapped in 'try/catch' for safe error handling

4. **No Permanent File Storage**
   - File is uploaded temporarily for inspection only
   - No custom entities or database interaction

## Sample Output

After submission, the following details are shown:

![alt text](image.png)


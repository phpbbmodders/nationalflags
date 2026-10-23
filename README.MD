# National Flags for phpBB3

phpBB extension that allows users to be able to choose a national flag and have a top (those with the most users selecting the flag) number of flags display on the index page of a phpBB forum.  Header is link to a listing of all users of a flag.  Clicking on user count of the flag displays the users that have that flag set.  Extension requires at least phpBB version 3.3.

[![Build Status](https://github.com/rmcgirr83/nationalflags/workflows/Tests/badge.svg)](https://github.com/rmcgirr83/nationalflags/actions)

Index Page
![Screenshot](index_page.jpg)

Custom Flag Page
![Screenshot](all_flags_of_users.jpg)

Users of a Flag
![Screenshot](users_of_flag.jpg)

## Requirements

- phpBB 3.3.19 or later
- PHP 8.3.36 or later

## Installation

### 1. clone
Clone (or download and move) the repository into the folder ext/rmcgirr83/nationalflags:

```
cd phpBB3
git clone https://github.com/rmcgirr83/nationalflags.git ext/rmcgirr83/nationalflags/
```

### 2. activate
Go to admin panel -> tab customise -> Manage extensions -> enable National Flags

Within the Admin panel visit the Extensions tab and choose the settings (allow, require on registration, display message) for the extension.

## Update instructions:
1. Go to your phpBB Board > Admin Control Panel > Customise > Manage extensions > National Flags: disable
2. Delete all files of the extension from ext/rmcgirr83/nationalflags EXCEPT for the flags directory
3. Upload all the new files to the same locations
4. Go to your phpBB Board > Admin Control Panel > Customise > Manage extensions > National Flags: enable

## Country URLs

Member listings use country codes from unique flag image filenames, for example
`/flags/US`, `/flags/CA`, and `/flags/MX`. Codes are case-insensitive; `USA` also
opens the `US` flag unless a separate flag uses the `USA` code. Regional codes
such as `GB-ENG` are supported. Pagination keeps the country code in the URL.

Existing numeric URLs continue to work. Custom flags without a unique code
(two or three letters, optionally followed by a hyphen and two or three letters)
keep numeric URLs. Renaming a flag image can change its code URL; numeric links
remain tied to the stored flag ID. Board permissions still control access.

### Uploaded flag images

New ACP uploads are stored with unique filenames in `store/rmcgirr83_nationalflags/` and served through phpBB. Include this directory in board backups and preserve it during extension updates. The board’s `store/` directory must be writable to upload images.

Existing images in the extension’s `flags/` directory continue to work. Editing or deleting a flag never deletes these bundled or manually installed images. Managed uploads are removed only when no flag references them. Custom uploaded flags retain numeric member-list URLs.

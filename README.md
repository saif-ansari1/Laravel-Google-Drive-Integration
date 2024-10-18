## About Laravel-Google-Drive-Integration

Connect Google Drive in Laravel and upload images directly through Google Drive. Below are some features highlighted:

- Connect Google Drive for each user
- Upload multiple images directly from Google Drive
- Download uploaded images in Multiple Size and Extension

## Steps to Setup:
- composer i
- php artisan migrate
- php artisan storage:link
- npm run i
- npm run build
- Insert below values in .env

1. GOOGLE_CLIENT_ID=
2. GOOGLE_CLIENT_SECRET=
3. GOOGLE_REDIRECT_URI=

A) Create a Project in Google Cloud Console:
- Go to the Google Cloud Console.
- Sign in with your Google account and click on the Select a project dropdown.
- Click New Project to create a new project and give it a name.
- After creating the project, select it.

B) Enable APIs and Services:
- In the Google Cloud Console, navigate to APIs & Services > Library.
- Search for Google Drive API and click Enable.

C) Obtain GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET:

a. Configure OAuth Consent Screen:
	- Go to APIs & Services > OAuth consent screen.
	- Select whether your app is for Internal (G Suite users) or External (any Google account).
	- Fill in the required details like the app name, user support email, etc.
	- Under "Scopes for Google APIs," you can add any required scopes (like email, profile, etc.).
	- Add your domain (if any) and save.

b. Create OAuth 2.0 Credentials:
	- Go to APIs & Services > Credentials.
	- Click on Create Credentials and select OAuth 2.0 Client ID.
	- Add 'Google Drive API' in non-sensitive scopes
	- Add testing users email

D) Get GOOGLE_API_KEY:
- Go to APIs & Services > Credentials.
- Click Create OAuth client ID.
- Fill in the required details like the app name, user support email, etc.
- Authorized redirect URIs http://127.0.0.1:8000/google-drive/callback
- The generated Client ID and Client secret will be there.

## License

The is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
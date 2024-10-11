## About Laravel-Google-Drive-Integration

Connect Google Drive in Laravel and upload images directly through Google Drive. Below are some features highlighted:

- Connect Google Drive for each user
- Upload multiple images directly from Google Drive
- Download uploaded images in Multiple Size and Extension

## Steps to Setup:
- composer i
- php artisan migrate
- php artisan storage:link
- Insert below values in .env

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
GOOGLE_API_KEY=

1. Create a Project in Google Cloud Console:
- Go to the Google Cloud Console.
- Sign in with your Google account and click on the Select a project dropdown.
- Click New Project to create a new project and give it a name.
- After creating the project, select it.

2. Enable APIs and Services:
- In the Google Cloud Console, navigate to APIs & Services > Library.
- Search for the Google API that you need (e.g., Google Drive API, Google People API, Google Maps API, etc.), and click Enable.

3. Obtain GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET:
a. Configure OAuth Consent Screen:
	- Go to APIs & Services > OAuth consent screen.
	- Select whether your app is for Internal (G Suite users) or External (any Google account).
	- Fill in the required details like the app name, user support email, etc.
	- Under "Scopes for Google APIs," you can add any required scopes (like email, profile, etc.).
	- Add your domain (if any) and save.
b. Create OAuth 2.0 Credentials:
	- Go to APIs & Services > Credentials.
	- Click on Create Credentials and select OAuth 2.0 Client ID.
	- Choose Web Application and fill in the required information, such as:
	- Authorized Redirect URIs: This is where Google will redirect after authentication. Example: https://yourdomain.com/auth/callback.
	- After creating the credentials, you’ll get the Client ID and Client Secret.

4. Get GOOGLE_API_KEY:
- Go to APIs & Services > Credentials.
- Click Create Credentials and select API Key.
- The generated API key will appear, and you can copy it.

5. Get GOOGLE_REDIRECT_URI:
This is the URI where Google redirects users after they authenticate. You specify it when setting up OAuth credentials:
Example: https://yourdomain.com/auth/callback.
Ensure that the GOOGLE_REDIRECT_URI is added in the Authorized Redirect URIs section when creating your OAuth credentials in the Credentials section.

## License

The is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
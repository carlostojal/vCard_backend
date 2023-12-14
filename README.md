# vCard_backend

## Passport configuration

⚠️ You need to configure passport in order to be able to obtain access tokens. This is an essential step. ⚠️

- Install passport using `artisan passport:install`
- Create a passport client for vCards `artisan passport:client --passport`
    - Name the client `client_vcard`;
    - Take note of the assigned ID and secret;
    - Place the ID and secret on the `.env` file under `VCARD_CLIENT_ID` and `VCARD_CLIENT_SECRET`.
- Create a passport client for users `artisan passport:client --passport`
    - Name the client `client_user`;
    - Take note of the assigned ID and secret;
    - Place the ID and secret on the `.env` file under `USER_CLIENT_ID` and `USER_CLIENT_SECRET`.
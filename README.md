# Arengu Auth PrestaShop module
This module enables custom signup, login and passwordless endpoints to interact with PrestaShop's authentication system from [Arengu flows](https://www.arengu.com/flows/).

Note that this module currently only allows you to manage the accounts of your customers and **not your employees'**.

## Simple installation
1. Download the [latest release ZIP file](https://github.com/arengu/ps_arengu_auth/releases/latest).
2. Upload it to your admin panel in **Modules > Module Manager > Upload a module**.
3. You can now find **Arengu Auth** module in the "Other" category.
4. Click "Configure" to get your API key.

## Manual installation
1. Download the [latest release ZIP file](https://github.com/arengu/ps_arengu_auth/releases/latest) and **extract** it inside the `modules` directory of an existing PrestaShop installation. You should end with a file structure where you can locate the `ps_arengu_auth.php` file **precisely** in `modules/ps_arengu_auth/ps_arengu_auth.php`. Otherwise, the module will not be detected by PrestaShop.
2. Go to the admin panel, **Modules > Module Manager**, and find "Arengu Auth" in the "Other" category.
3. Click "Install".
4. Click "Configure" to get your API key.

## Available endpoints

These are all the operations exposed by this module:

- [Private endpoints](#private-endpoints)
  1. [Sign up](#sign-up)
  2. [Log in](#log-in)
  3. [Passwordless](#passwordless)
  5. [Check existing email](#check-existing-email)
- [Public endpoints](#public-endpoints)
  1. [Log in with JWT](#log-in-with-jwt)


### Private endpoints

The private part of the API is protected by an API key. You can view and manage your API key under your module settings, in the PrestaShop admin panel.

> **Warning:** This API key **allows to impersonate any customer in your store, so you must keep it secret and do not share it in publicly accessible areas such as GitHub, client-side code, and so forth.**

Authentication to the API is performed via `Authorization` header with `Bearer` schema:

```
Authorization: Bearer YOUR_API_KEY
```

#### Sign up

Sign up users with email and password or just with an email (passwordless signup).

```
POST /module/ps_arengu_auth/signup
Content-Type: application/json
```

##### Request payload

| Property | Type | Description |
| ------ | ------ | ------ |
| firstname _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's first name. |
| lastname _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's last name. |
| email _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's email. |
| password _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's plain password. If you don't provide a password, a random one will be generated. This is useful if you want to use passwordless flows. |
| expires_in _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number) | Number of seconds that the JWT will be valid. By default it's 300 (5 minutes). |
| redirect_uri _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The URL where you want to redirect the user after logging him in when you send him to the JWT verification endpoint. By default it's the user account page. |

##### Operation example
```
> POST /module/ps_arengu_auth/signup
> Content-Type: application/json
{
  "firstname": "Jane",
  "firstname": "Doe",
  "email": "jane.doe@arengu.com",
  "password": "foobar"
}

< HTTP/1.1 200 OK
< Content-Type: application/json
{
  "user": {
    "id": 1,
    "email": "jane.doe@arengu.com",
    "firstname": "Jane",
    "lastname": "Doe",
    "birthday": null,
    "id_gender": null,
    "company": null,
    "newsletter": null,
    "optin": null,
    "default_group": 3,
    "groups": [
      3
    ]
  },
  "token": "...",
  "login_url": "..."
}
```

#### Log in

Log in users with email and password.

```
POST /module/ps_arengu_auth/login_password
Content-Type: application/json
```

##### Request payload

| Property | Type | Description |
| ------ | ------ | ------ |
| email _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's email you want to sign up. |
| password _(required)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | Query selector or DOM element that the form will be appended to. |
| expires_in _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number) | Number of seconds that the JWT will be valid. By default it's 300 (5 minutes). |
| redirect_uri _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The URL where you want to redirect the user after logging him in when you send him to the JWT verification endpoint. By default it's the user account page. |

##### Operation example

```
POST /module/ps_arengu_auth/login_password
Content-Type: application/json
{
  "email": "jane.doe@arengu.com",
  "password": "foobar"
}

< HTTP/1.1 200 OK
< Content-Type: application/json
{
  "user": {
    "id": 1,
    "email": "jane.doe@arengu.com",
    "firstname": "Jane",
    "lastname": "Doe",
    "birthday": null,
    "id_gender": null,
    "company": null,
    "newsletter": null,
    "optin": null,
    "default_group": 3,
    "groups": [
      3
    ]
  },
  "token": "...",
  "login_url": "..."
}
```
#### Passwordless

Authenticate users without password.

```
POST /module/ps_arengu_auth/passwordless_login
Content-Type: application/json
```

> **Warning:** This endpoint was designed to be invoked once the user identity is verified using, at least, one authentication factor (eg. one-time password via email or SMS, social login, etc).

#### Request payload

| Property | Type | Description |
| ------ | ------ | ------ |
| email _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's email you want to authenticate. |
| expires_in _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number) | Number of seconds that the JWT will be valid. By default it's 300 (5 minutes). |
| redirect_uri _(optional)_ | [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The URL where you want to redirect the user after logging him in when you send him to the JWT verification endpoint. By default it's the user account page. |

##### Operation example
```
> POST /module/ps_arengu_auth/passwordless_login
> Content-Type: application/json
{
  "email": "jane.doe@arengu.com"
}

< HTTP/1.1 200 OK
< Content-Type: application/json
{
  "user": {
    "id": 1,
    "email": "jane.doe@arengu.com",
    "firstname": "Jane",
    "lastname": "Doe",
    "birthday": null,
    "id_gender": null,
    "company": null,
    "newsletter": null,
    "optin": null,
    "default_group": 3,
    "groups": [
      3
    ]
  },
  "token": "...",
  "login_url": "..."
}
```

#### Check existing email

Check if an email exists in your database.

```
POST /module/ps_arengu_auth/check_email
Content-Type: application/json
```

##### Request payload

| Property | Type | Description |
| ------ | ------ | ------ |
| email _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | The user's email. |

##### Operation example
```
> POST /module/ps_arengu_auth/check_email
> Content-Type: application/json
{
  "email": "jane.doe@arengu.com"
}

< HTTP/1.1 200 OK
< Content-Type: application/json
{
  "email_exists": true
}
```

### Public endpoints

#### Log in with JWT

Make a user to be logged in by redirecting him to this URL with a signed JWT that you previously received as a response in a signup or login request.

`GET` **/module/ps_arengu_auth/login_jwt**

##### URL parameters

| Parameter | Type | Description |
| ------ | ------ | ------ |
| token _(required)_| [String](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String) | A signed JSON web token (JWT), containing `sub` (the user ID), `email` (the user email) and optionally `redirect_uri` with the absolute or relative URL the user will be redirected after the login. If the latter is not specified, the user will be redirected to the home page. |

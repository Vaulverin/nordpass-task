# Secure Information Storage REST API

### Project setup

* Add `secure-storage.localhost` to your `/etc/hosts`: `127.0.0.1 secure-storage.localhost`

* Run `make init` to initialize project

* Open in browser: http://secure-storage.localhost:8000/item Should get `Full authentication is required to access this resource.` error, because first you need to make `login` call (see `postman_collection.json` or `SecurityController` for more info).

### Run tests

make tests

### API credentials

* User: john
* Password: maxsecure

### Postman requests collection

You can import all available API calls to Postman using `postman_collection.json` file

### API specification
#### [POST] `/login`
Creates user session in COOKIES.\
Headers:
 - Content-type: `application/json`

Body (json):
 ```json
 {
    "username": "john",
    "password": "maxsecure"
}
 ```
####Response:
Status: `200 OK`\
Body:
```json
{
    "username": "john",
    "roles": [
        "ROLE_USER"
    ]
}
```

#### [POST] `/logout`
Deletes user session.\
Headers:
- Content-type: `application/json`

Body (json):
 ```json
 {
    "username": "john",
    "password": "maxsecure"
}
 ```
####Response:
Status: `200 OK`\
Right now it redirects to the root page, which does not exist, so it gives 404 error.

#### [GET] `/item`
Returns all items for logged in user.\
####Response:
Status: `200 OK`\
Body:
```json
[
  {
    "id": 77,
    "data": "new secret",
    "created_at": "2021-08-25T10:10:44+00:00",
    "updated_at": "2021-08-25T10:11:18+00:00"
  }
]
```

#### [POST] `/item`
Creates new item.\
Headers:
- Content-type: `multipart/form-data;`

Body (form data): `data="new item secret"`
####Response:
Status: `200 OK`\
Body: `[]`

#### [PUT] `/item`
Updates existing item.\
Headers:
- Content-type: `multipart/form-data;`

Body (form data): 
 - `id="77"`
 - `data="new secret"`
####Response:
Status: `200 OK`\
Body: `[]`


#### [DELETE] `/item/<id>`
Deletes existing item by ID.
####Response:
Status: `200 OK`\
Body: `[]`

### API Errors
Response body format:
```json
{
  "error": "<error message>"
}
```
# Responses

The data you return with the response will be rendered under `data` key in the resulting JSON.

## Example response

```php
return new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]);
```

The resulting JSON:

```json
{
  "data": {
    "first_name": "Jerry",
    "last_name": "Cotton"
  }
}
```

## Response code

The default response code is `200`. Use `code()` to specify custom response code.

```php
return (new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]))->code(201);
```

## Additional top level keys

Sometimes you need to return additional key-value pairs, such as a message, alongside the data. In this example response data contains user details, and the message would not belong there. Use `with()` to define additional keys.

```php{4-6}
return (new Response([
  'first_name' => 'Jerry',
  'last_name' => 'Cotton',
]))->with([
  'message' => 'Your details were saved successfully!',
]);
```

The resulting JSON:

```json
{
  "data": {
    "first_name": "Jerry",
    "last_name": "Cotton"
  },
  "message": "Your details were saved successfully!"
}
```

# Keboola HTTP Extractor
 
[![Build Status](https://travis-ci.com/keboola/http-extractor-with-placeholders.svg?branch=master)](https://travis-ci.com/keboola/http-extractor-with-placeholders)
[![Maintainability](https://api.codeclimate.com/v1/badges/43e8dd8c4f2e48165160/maintainability)](https://codeclimate.com/github/keboola/http-extractor-with-placeholders/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/43e8dd8c4f2e48165160/test_coverage)](https://codeclimate.com/github/keboola/http-extractor-with-placeholders/test_coverage)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/keboola/http-extractor-with-placeholders/blob/master/LICENSE.md)

> Custom version with placeholder support

Download files from any public URL to `/data/out/files`. 

## Configuration options

- `baseUrl` (required) -- common part of URL
- `path` (required) -- path part of URL (futureproof to allow row configs)
- `maxRedirects` (optional) -- maximum number of redirects to follow
- `placeholders` (optional) -- replacement of placeholders in `baseUrl` and `path` with result of user defined scripts

### Placeholders
Placeholders are user defined scripts in [Keboola CodeBuilder](https://github.com/keboola/php-codebuilder) syntax. **Nested functions are not supported**.

#### Alowed functions
- `md5`: Generate a md5 key from its argument value
- `sha1`: Generate a sha1 key from its argument value
- `time`: Return time from the beginning of the unix epoch in seconds (1.1.1970)
- `date`: Return date in a specified format
- `strtotime`: Convert a date string to number of seconds from the beginning of the unix epoch
- `base64_encode`
- `hash_hmac`: [See PHP documentation](http://php.net/manual/en/function.hash-hmac.php)
- `sprintf`: [See PHP documentation](http://php.net/manual/en/function.sprintf.php)
- `concat`: Concatenate its arguments into a single string


### Sample configurations

#### Minimal config

```json
{
    "parameters": {
        "baseUrl": "https://www.google.com/",
        "path": "favicon.ico"
    }
}
```

This will save Google favicon into `/data/out/files/favicon.ico`. 

#### Placeholders usage

```json
{
    "parameters": {
        "baseUrl": "https://www.example.com/",
        "path": "my-api.json?since={YESTERDAY_TIMESTAMP}",
        "placeholders": {
            "{YESTERDAY_TIMESTAMP}": {
                "function": "strtotime",
                "args": ["-1 day"]
            }
        }
    }
}
```

## Development

- Install Composer packages

```
docker-compose run --rm dev composer install --prefer-dist --no-interaction
```

- Get contents for `data/` directory using [Sandbox API call](https://developers.keboola.com/extend/common-interface/sandbox/) (replace `YOURTOKEN` with your Storage API token). 

```
curl --request POST --url https://syrup.keboola.com/docker/sandbox --header 'Content-Type: application/json'   --header 'X-StorageApi-Token:YOURTOKEN' --data '{"configData": { "parameters": {"baseUrl": "https://www.google.com/","path": "favicon.ico"}}}'
```


- Run the extractor 

```
docker-compose run --rm dev
```

### Tests
Run the complete CI build

```
docker-compose run --rm dev composer ci
```

or just the tests

```
docker-compose run --rm dev composer tests
```

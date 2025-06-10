# ProcessWire JSON API

**NOT READY FOR PRODUCTION USE**

[Click here for documentation](https://fokke-.github.io/pw-json-api/)

## Tests

DDEV must be installed in order to run tests. See https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/

Running this command starts the container, imports the database and runs the tests. The container will keep running after the tests are complete.

```console
composer run test
```

### Stop the container

```console
ddev stop
```

### Admin panel of the test environment

The admin panel can be accessed at [https://pw-json-api.ddev.site](https://pw-json-api.ddev.site).

- User: `testuser`
- Pass: `testuser`

### Exporting database

```console
# While the container is running
composer run export-test-db
```

The database dump will be saved to `./tests/fixtures/test-db.sql.gz`

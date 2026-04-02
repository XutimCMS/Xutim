# Tests

Run all tests:

```bash
make test
```

Run specific tests:

```bash
make test PHPUNIT_FLAGS='--filter="TagRepositoryTest"'
```

The test app lives in `tests/Application/` with its own kernel, entities, and `.env`.
Factories are in `tests/Application/src/Factory/`.
Application tests go in `src/Xutim/Bundle/*/tests/Application/`.

After schema changes, update the test DB:

```bash
cd tests/Application && bin/console doctrine:schema:update --force
```

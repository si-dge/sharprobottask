# Playwright E2E tests

The test covers the full admin flow:

1. Log in as `admin` / `admin`.
2. Open the import page and upload `../import_example.xlsx`.
3. Wait two minutes for the asynchronous import worker.
4. Open the product list, verify that it contains a product, and open its card.

## Run

Start the application first:

```bash
./build.sh
./setup.sh
```

Install the test dependencies and Chromium:

```bash
cd tests
npm install
npx playwright install chromium
```

Run the test against the Docker application at `http://localhost`:

```bash
npm test
```

To use another application URL:

```bash
BASE_URL=http://localhost:4200 npm test
```

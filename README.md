The README.md file content is generated automatically, see [Magento module README.md](https://github.com/magento/devdocs/wiki/Magento-module-README.md) for more information.

# VML_CustomerImport module

The `VML_CustomerImport` module for Magento 2 allows you to import customers from CSV and JSON files via a CLI command. It provides flexibility to support different import profiles, making it suitable for various data formats.

## Installation details

### Step 1: Composer Installation
You can install the module via Composer. Run the following commands in your Magento 2 root directory:

    composer require vml/module-customer-import
    php bin/magento module:enable VML_CustomerImport
    php bin/magento setup:upgrade

### Step 2: Verify Installation
Verify that the module is enabled by running:
    
    php bin/magento module:status
You should see `VML_CustomerImport` in the list of enabled modules.

## Usage
### Import Customers
To import customers from a CSV or JSON file, use the CLI command customer:import followed by the profile name and source file path:
    
    php bin/magento customer:import sample-csv sample.csv
    php bin/magento customer:import sample-json sample.json

Replace sample-csv and sample.json with your specific profile names and file paths.

## Additional Notes
 * The module assumes customers are imported to the default website and customer group.
 * Ensure your CSV and JSON files are correctly formatted according to Magento's customer import requirements.

## Contributing
Contributions are welcome! Feel free to submit issues and pull requests.

## License
This module is licensed under the [OSL 3.0 License](). 

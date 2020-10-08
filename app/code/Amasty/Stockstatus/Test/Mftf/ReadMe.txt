Current version of tests is for Magento CE 2.3.4 with Amasty Custom Stock Status 1.4.* only.
Release version consists of 8 smoke tests.
In order to receive correct run of image checkings it is necessary to store image (required image is stored with ReadMe.txt file) in magento_root/dev/tests/acceptance/tests/_data folder.
The tests are divided into following group:
- Stockstatus (is used for running of all tests. E.g. vendor/bin/mftf run:group Stockstatus -r)



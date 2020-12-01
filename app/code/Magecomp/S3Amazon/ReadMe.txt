To Sync All Your Media Files To Amazon S3 Server
===========================================================
==> You must have to follow below steps

Note : Before Use Amazon S3 As your storage file take backup of your media (pub/media)

1. First Enable Amazon S3 Extension From Backend And Setup Your Amazon S3 Credentials Details

==> Run All Commands To Your Magento Root Path

2. Run Bellow Command To Upload All Your Pre-Existing Uploaded Images, e.g. category,product images, etc., to Amazon S3 Server

	php bin/magento amazons3:export

3. To Enable S3 Integeration Run Below Command

	php bin/magento amazons3:enable

4. To Disable S3 Integeration Run Below Command

	php bin/magento amazons3:disable



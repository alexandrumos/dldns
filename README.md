DLDNS - Dinamic Linode DNS
======

## About
I created this simple tool to automatically update DNS entries for domains hosted at Linode. Uses [Linode API v4](https://developers.linode.com/v4/introduction) to update domain records to a specified target. It's used in combination with `cron` to update IPs after they change.


## How to install it
Download or clone this project. Rename `.env.example` file to `.env`. Make sure you set the `LINODE_PAT` to your Linode personal access token.


## How to obtain a Personal Access Token
Sign into the new [Linode Manager](https://cloud.linode.com). Go to `My Profile` and `API Tokens`.

![alt text](https://s3.eu-central-1.amazonaws.com/dldns/api_tokens.jpg "API Tokens")

Create a new Personal Access Token. Make sure to provide proper access level for `Domains`.

![alt text](https://s3.eu-central-1.amazonaws.com/dldns/permissions.jpg "API Token Permissions")

Get the token and put it in `.env` file.

![alt text](https://s3.eu-central-1.amazonaws.com/dldns/token.jpg "Your Access Token")


## Usage instructions
### helper-domains.php
This script shows a table with all your Linode domains:
```bash
php helper-domains.php
```
E.g.:

![alt text](https://s3.eu-central-1.amazonaws.com/dldns/helper_domains.jpg "Domains Helper")

### helper-records.php
Lists all the records from a domain. Requires the domain ID provided as argument: `domain_id=123456`.
```bash
php helper-records.php domain_id=123456
```
E.g.:

![alt text](https://s3.eu-central-1.amazonaws.com/dldns/helper_records.jpg "Records Helper")

### update-record.php
Updates the target for a specified record. Requires domain ID (`domain_id=123456`) and record ID (`record_id=9876543`) provided as arguments. Also a target IP (`target_ip=1.1.1.1`) argument is optional. If provided sets the record target to the argument value. Otherwise obtains the system's public IP address and uses it as target:
```bash
php update-record.php domain_id=123456 record_id=9876543
php update-record.php domain_id=123456 record_id=9876543 target_ip=123.234.211.211
```
E.g.

![alt text](https://s3.eu-central-1.amazonaws.com/dldns/update.jpg "Update Record")
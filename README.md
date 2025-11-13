# Vtiger CRM

Vtiger is a PHP based web application that enables businesses to increase sales wins, marketing ROI, and support satisfaction by providing tools for employees and management work more effectively, capture more data, and derive new actionable insights from across the customer lifecycle.

## Get involved

Development on vtiger is done at https://code.vtiger.com

**Note**: Any contributions submitted to Vtiger project should be made available under Vtiger Public License.
If contribution has any patented code, or commercial code, then please communicate with Vtiger team before making the contribution.

https://www.vtiger.com/vtiger-public-license/

To register for an account, please contact community @ vtiger.com, you will need this to file issues and/or fix the code
Once you have an account, you can [browse the code](https://code.vtiger.com/vtiger/vtigercrm/tree/master),
[see if your issue is already reported](https://code.vtiger.com/vtiger/vtigercrm/issues) and if you have a new problem
to report you can [create an issue](https://code.vtiger.com/vtiger/vtigercrm/issues/new?issue)

If you then want to fix the issue (or another issue) you can create your own fork of vtiger to work on using the
fork button on the vtiger project, this will create a new git repository for you at

    https://code.vtiger.com/yourname/vtigercrm.git

on your computer you will need a git client installed and you need to tell git who you are:

    git config --global user.name "YOUR NAME"
    git config --global user.email "YOUR EMAIL ADDRESS"

now clone your fork of vtiger

    git clone https://code.vtiger.com/yourname/vtigercrm.git

this will pull down from the server your copy of the vtiger code and all the history.

You will make a new branch for your changes, you can give it a descriptive name, once the branch is created
you will switch to that branch using the checkout command

    git branch fix_projects_on_calendar
    git checkout fix_projects_on_calendar

Before you install, you need to run `composer update`

Now you can make your changes and commit all changed files with

    git commit -a

Do reference the issue number in your commit message, e.g. "fix #2 display projects on the calendar" the number will
allow the system to link the commit to the issue.

Now you can push your branch to the server, this creates the branch on the server end and populates it

    git push --set-upstream origin fix_projects_on_calendar

look at the branch on code.vtiger.com and create a merge request from your branch
to the upstream master, this will be reviewed to see if it fixes the
issue and if all is good will be merged into the upstream code.
You can then switch back to your master branch with

    git checkout master

And you can create additional feature branches from there to fix different things.

If there have been other changes to the central vtiger code that you want in your work area then you can add the central
repository as an upstream remote (only need to do this bit once), then you can fetch changes and merge them

    git remote add upstream https://code.vtiger.com/vtiger/vtigercrm.git
    git fetch upstream
    git merge upstream/master

Deploy in github with export/import database

1. chmod +x deploy.sh // Only first time
2. ./deploy.sh


3. Pull all changes from github repo: `sudo git pull origin main`
4. Install new packages if need it `composer install`
5. Give permissions:
     `sudo chown -R $USER:$USER /var/www/html`
     `sudo chmod -R 775 /var/www/html`
6. Login to mysql `mysql -u vtigeruser -p` after that enter the password
    6.1. Use databasel `USE vtiger_gpm;` 
7. import database (if it neeed) add file only with `_changes_` in the name `mysql -u vtigeruser -p vtiger_gpm < db_backups/vtiger_gpm_changes_2025_11_05_1621.sql`
8. Restart apache server `sudo service apache2 restart`

9. If VM instalation failed and got 'Access to restricted file' 
    9.1 Check in config file: $dbconfig['db_server'] = 'localhost';
                                $dbconfig['db_port'] = ':3306';
                                $dbconfig['db_username'] = 'root';
                                $dbconfig['db_password'] = '';
                                $dbconfig['db_name'] = 'vtiger_gpm';
                                $dbconfig['db_type'] = 'mysqli';
                                $dbconfig['db_status'] = 'true';
                                $site_URL = 'http://localhost/vtiger-gpm/';
                                $root_directory = '/var/www/html/';

10. Clear cache: `rm -rf test/templates_c/v7`
   10.1 After that you should create this folder `mkdir -p test/templates_c/v7`
   10.2 Perrmission to this folder: `sudo chown -R www-data:www-data test/templates_c`, `sudo chmod -R 775 test/templates_c`

11. Open config.ini  file `nano config.inc.php`

12. count how many times cron ran with log file `grep -c "Cron job completed" logs/order_cron.log`

13. Check the system’s cron logs (server-wide) `sudo grep CRON /var/log/syslog`

14. Change field in CRM
    14.1 First select table and field id from table based on label `SELECT fieldid, fieldlabel, fieldname, columnname, tablename, typeofdata FROM vtiger_field WHERE fieldlabel = 'Indicative FX spot';`
    14.2 Change database column to TEXT (if you wanna change other type must add type) `ALTER TABLE vtiger_gpmintent MODIFY COLUMN indicative_fx_spot VARCHAR(255);` 
    14.3 Update field metadata `UPDATE vtiger_field SET typeofdata = 'V~O' WHERE fieldid = 1035;`
    14.4 Change UI type (VERY IMPORTANT) `UPDATE vtiger_field SET uitype = 1 WHERE fieldid = 1035;`

   

# Cron Job Setup Instructions

This document explains how to set up the automatic updating system for the investment platform. The `cron_updates.php` script handles all of the processing of investment returns, staking rewards, and account balance updates.

## What the Cron Job Does

The `cron_updates.php` script performs the following tasks:

1. Processes investment returns when they are due
2. Processes staking rewards and handles compounding if enabled
3. Updates user balances with returns and rewards
4. Marks investments and stakings as completed when they reach their end date
5. Updates when stakes can be unstaked (after lock period)
6. Processes referral commissions

## Setting Up the Cron Job

### Linux/Unix Servers

On Linux servers, you can use the crontab to schedule the script to run at regular intervals.

1. Open your crontab for editing:
   ```
   crontab -e
   ```

2. Add a line to run the script every hour (recommended):
   ```
   0 * * * * /usr/bin/php /path/to/investment_script/cron_updates.php
   ```
   
   Replace `/path/to/investment_script` with the actual path to your installation.

3. Save and exit the editor.

### Windows Server

On Windows, you can use the Task Scheduler:

1. Open Task Scheduler (search for "Task Scheduler" in the Start menu)
2. Click "Create Basic Task..."
3. Give it a name like "Investment Platform Updates"
4. Select "Daily" as the trigger
5. Set it to recur every 1 hour
6. Choose "Start a program" as the action
7. Browse for the PHP executable (e.g. `C:\php\php.exe`)
8. Add arguments: `-f "C:\path\to\investment_script\cron_updates.php"`
9. Complete the wizard, then right-click on the created task and select "Properties"
10. On the Settings tab, check "Run task as soon as possible after a scheduled start is missed"
11. Click OK to save

### Shared Hosting

On shared hosting, use the control panel's cron job feature:

1. Log into your hosting control panel (cPanel, Plesk, etc.)
2. Find the "Cron Jobs" or "Scheduled Tasks" section
3. Set up a new cron job with the following settings:
   - Command: `php /home/username/public_html/investment_script/cron_updates.php`
   - Frequency: Every 1 hour
   
   Replace `/home/username/public_html` with your actual path.

## Testing the Cron Setup

To test if your cron setup is working properly:

1. Run the script manually first:
   ```
   php cron_updates.php
   ```

2. Check the logs in the `logs` directory to verify it's running correctly

3. After setting up the cron job, check the logs again in an hour to see if it ran automatically

## Troubleshooting

If the cron job isn't running:

1. Check if the log directory is writable
2. Verify the PHP path is correct
3. Ensure the script has execution permissions (chmod +x cron_updates.php on Linux/Unix)
4. Check your server's cron logs for any errors

If investment returns or staking rewards aren't being processed:

1. Check the database to verify the expected_date fields have the correct dates
2. Verify the status fields are set to 'pending'
3. Manually run the script with verbose output to debug issues

## Maintenance

- Review the log files periodically to ensure the script is running properly
- Consider implementing log rotation to prevent the logs from growing too large
- Update the script if you make changes to the investment or staking logic 
## Integration with Freeside v3 or v4 service exports

One available export using this set of scripts is build for Freeside [!website](https://github.com/freeside/Freeside) v3 or v4

## Brief ramblings

Freeside is setup in such a way that it runs most of the Peal daemons as the freeside system user.

Exports are created and attached to the provisioned services under packages billed out to customers.

## Requirements

First, read the requirements found in the main README.md file before continuing.

For this to work, your freeside server has to have shared pass-key access to a maching that has this repo installed.  The easiest way for me to get this working without requireing an addition remote machine to work on, it to setup and configure the package to run in the home directory of the freeside user on the freeside server itself.

When freeside was installed, more than likely it generated a public key for the freeside user.  Verify this by looking in the freeside user home dir:

```
  sudo ls -l /home/freeside/.ssh/
```

If you see the id_rsa.pub file, you are ready to move on to the next step.

You will want to setup the freeside user to be able to log into the localhost as himself.  To do this, you will need to make sure that the generated ssh key in the ~/.ssh/id_rsa.pub file is contained in the ~/.ssh/authorized_keys.  As the freeside user, issue this command:

```
  cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
```

Now, as the freeside user, verify that you can log in to localhost without being required to enter a password.

```
  ssh freeside@localhost
```

If this does not allow you to access without a password, you need to go over the above insturctions again.

Once you have this working, move on to configuring Freeside to use this Preseem API.

## Installation

First, read the requirement found in the main README.md file before continuing.

You will need to know the path to the exports folder for where you installed the repo.  So, if you installed the repo to /home/freeside/preseem_api/  You will need to use ./preseem_api/export/freeside.php as the executable script in your command in the fields below.

To use this repo in your exports, you will need to create a new Service Export and attach it to the service you are using to track your customers internet connection information.  I built this export around the svc_broadband service type.  No other service type has all the required fields used in the Preseem API.

In the Freeside UI, browse to Configuration -> Services -> Provisioning Exports.  Click on "Add a new export" and follow along below:

```
  Export Name: [Give it a good name]
       Export: Choose "broadband_shellcommands" option

           Host or IP: localhost
          Remote User: freeside
       Insert Command: ./preseem_api/export/freeside.php --action=insert --service_id='$description' --account_id=$custnum --account_name='$description' --service_speed_up=$speed_up --service_speed_down=$speed_down
       Delete Command: ./preseem_api/export/freeside.php --action=delete --service_id='$description'
 Modification Command: ./preseem_api/export/freeside.php --action=replace --service_id='$new_description' --old_service_id='$old_description' --account_id=$new_custnum --old_account_id=$old_custnum --account_name='$new_description' --service_speed_up=$new_speed_up --service_speed_down=$new_speed_down
   Suspension Command: ./preseem_api/export/freeside.php --action=suspend --service_id='$description' --account_id=$custnum --account_name='$description' --service_speed_up=1 --service_speed_down=1
 Unsuspension Command: ./preseem_api/export/freeside.php --action=unsuspend --service_id='$description' --account_id=$custnum --account_name='$description' --service_speed_up=$speed_up --service_speed_down=$speed_down
```
## Integration with Freeside v3 or v4 service exports

One available export using this set of scripts is build for [Freeside](http://freeside.biz) v3 or v4

## Brief ramblings

Freeside is setup in such a way that it runs most of the Peal daemons as the freeside system user.

Exports are created and attached to services which in turn are attached to packages ordered and billed on customer accounts.

## Requirements

Before proceeding, read the README.md file found in the base folder of this project.  It details what needs to be done prior to being able to use these scripts and has nothing to do with specific instructions for getting it to work with Freeside.  Those instructions follow...

Regarding Freeside specific requirements.  At this time, there are none.

## Installation

There happens to be two different methods this code base can be used with Freeside.  When executing the export Freeside can call the scripts via a remote ssh command execution call or by issuing an HTTP POST request.  I will provide the instructions for setting up the remote ssh command execution method.

- [SSH based remote code execution call](./README.freeside.ssh.md)
- [HTTP POST requrest](./README.freeside.http.md)

For Freeside to properly process the service export,  freeside server has to have shared pass-key access to a maching that has this repo installed.  The easiest way for me to get this working without requireing an addition remote machine to work on, it to setup and configure the package to run in the home directory of the freeside user on the freeside server itself.

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

## Freeside installation and configuration

First, read the requirement found in the main `README.md` file before continuing.

You will need to know the path to the exports folder for where you installed the repo.  So, if you installed the repo to `/home/freeside/preseem_api/`  You will need to use `./preseem_api/export/freeside.php` as the executable script in your command in the fields below.

To use this repo in your exports, you will need to create a new Service Export and attach it to the service you are using to track your customers internet connection information.  I built this export around the svc_broadband service type.  No other service type has all the required fields used in the Preseem API.  And no other service type has a compatible export type available to use.

In the Freeside UI, browse to Configuration -> Services -> Provisioning Exports.  Click on "Add a new export" and follow along below:

Export Name: [Give it a good name]
Export: Choose "broadband_shellcommands" option

Host or IP:

    localhost

Remote User:

    freeside

Insert Command:

    php ./preseem_api/exports/freeside.php --action=insert --service_id='$description' --account_id='$description' --custnum=$custnum --pkgnum='$pkgnum' --account_name='$description' --service_up_speed=$speed_up --service_down_speed=$speed_down --service_network_prefixes='$ip_addr' --service_cpe_mac='$mac_addr' --service_package='$pkgnum' --service_parent_device_id='$sectornum' --service_network_prefixes='$ip_addr' --service_cpe_mac='$mac_addr'

Delete Command:

    php ./preseem_api/exports/freeside.php --action=delete --service_id='$description' --account_id='$description' --custnum=$custnum --pkgnum='$pkgnum'

Modification Command:

    php ./preseem_api/exports/freeside.php --action=replace --service_id='$new_description' --old_service_id='$old_description' --pkgnum=$new_pkgnum --old_pkgnum=$old_pkgnum --account_id='$new_description' --custnum=$new_custnum --old_account_id='$old_description' --account_name='$new_description' --service_up_speed=$new_speed_up --service_down_speed=$new_speed_down --service_cpe_mac='$new_mac_addr' --service_network_prefixes='$new_ip_addr' --service_package='$new_pkgnum' --service_parent_device_id='$new_sectornum' --service_network_prefixes='$new_ip_addr' --service_cpe_mac='$new_mac_addr'

Suspension Command:

    php ./preseem_api/exports/freeside.php --action=suspend --service_id='$description' --pkgnum=$pkgnum --account_id='$description' --custnum=$custnum --account_name='$description' --service_up_speed=1 --service_down_speed=1 --service_network_prefixes='$ip_addr' --service_cpe_mac='$mac_addr' --service_package='$pkgnum' --service_parent_device_id='$sectornum' --service_network_prefixes='$ip_addr' --service_cpe_mac='$mac_addr'

Unsuspension Command:

    php ./preseem_api/exports/freeside.php --action=unsuspend --service_id='$description' --pkgnum=$pkgnum --account_id='$description' --custnum=$custnum --account_name='$description' --service_up_speed=$speed_up --service_down_speed=$speed_down --service_network_prefixes='$ip_addr' --service_cpe_mac='$mac_addr' --service_package='$pkgnum' --service_parent_device_id='$sectornum' --service_network_prefixes='$ip_addr' --service_cpe_mac='$mac_addr'

Now, make sure you attach this export to the existing svc_broadband service.  To do this, go to Configuration -> Services -> Service definitions  Click on the name of the internet service you want to have this export attached to.  On the "Edit Service Definition" page, just under the service Table type selection you should see the option to select your recently created export.

> Note: If the export is not listed, you will need to make sure you selected the correct export type in the previous step.

Once you locate the export, check the box next to its name.  Click 'Apply Changes' at the bottom of the page to save this change.

Ideally, you will have ssh access to the server where these scripts are installed.  This is the only way to monitor the logs that are generated by these scripts.  The default location for the logs generated is in the `logs/` subdirectory of the repository install path.  Again, as the freeside or root user follow the log file:

```
  sudo tail -f /home/freeside/preseem_api/logs/preseem.log
```

Navigate to one of your active customers that has a package with the service you just linked to the export.  No matter the state of the package, you can now provision, un-provision, suspend, un-suspend, or cancel the service.  Each of these actions will call their respective commands provisioned in the export.  It can take upwords of 60 seconds, but you should see something in the the `preseem.log` file after shortly.

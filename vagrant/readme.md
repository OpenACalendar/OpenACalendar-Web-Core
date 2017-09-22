# Vagrant Boxes

## The app box

This box runs the app in both single site and multi site mode. To use:

    vagrant up app
    vagrant ssh app
    php /vagrant/core/cli/createUser.php EMAIL PASSWORD sysadmin
    php /vagrant/core/cli/createSite.php test1 EMAIL

(Replace the placeholders USERNAME, EMAIL and PASSWORD with your own data.)

### Single Site Mode

The Single Site Mode app is then available at http://localhost:8082

### Multi Site Mode

If you also want to use Multi Site mode, then on the host machine you need to edit your hosts file.

    127.0.0.1    openadevcalendar.co.uk
    127.0.0.1    test1.openadevcalendar.co.uk
    127.0.0.1    test2.openadevcalendar.co.uk
    127.0.0.1    test3.openadevcalendar.co.uk
    127.0.0.1    test4.openadevcalendar.co.uk

Also run on the host box:

    php /vagrant/core/cli/createSite.php test2 EMAIL
    php /vagrant/core/cli/createSite.php test3 EMAIL
    php /vagrant/core/cli/createSite.php test4 EMAIL

The Multi Site Mode app is then available at http://openadevcalendar.co.uk:8080

Note the events that are available on http://test1.openadevcalendar.co.uk:8081 (Multi Site Mode) are the same ones that appear on http://localhost:8082 (Single Site Mode).

### User accounts

User accounts will not be verified. Click the link to send the email again, then check  /tmp/userVerifyEmail.txt to get the verify link.

### Testing on the app box

You can also run tests on this box, but this will run slowly and we recommend you use the tests vagrant box instead. But if you must, to run tests:

    vagrant ssh app
    ./test

## The tests box

This is a special box only for running tests. The Database files are saved on a RAM disk for speed. To use:

    vagrant up tests
    vagrant ssh tests
    ./test

## The Front End Tests box

To setup:

    vagrant up frontendtests
    vagrant ssh frontendtests
    ./run

Now to run tests, simply run

    ./test

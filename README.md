[![Circle CI](https://circleci.com/gh/Griesbacher/histou/tree/master.svg?style=svg)](https://circleci.com/gh/Griesbacher/histou/tree/master)
[![Coverage Status](https://coveralls.io/repos/Griesbacher/histou/badge.svg?branch=master&service=github)](https://coveralls.io/github/Griesbacher/histou?branch=master)
# Histou
#### Adds templates to Grafana in combination with [Nagflux](https://github.com/Griesbacher/nagflux).
Histou is designed to add templates to Grafana from Nagios Data. Therefor Nagflux sends the informations from Nagios/Icinga(2) to an InfluxDB. On the otherhand, Grafana is used to display this performancedata. Histou adds a custom dashboard to Grafana which will display dynamic dashboards, depending on the given host and service name in the URL. Histou fetches first additional informations from the InfluxDB and select afterwards a template. Templates can be JSON or PHP Files which contains a Grafana dashboard, the have also a rule, which defines when this template is used.

## Installation
### Dependencies
- Grafana
- Webserver with PHP 5.4+
- PHP commandline tool

### Webserver
- The whole Histoufolder accessible by copying to your webserver. On Debian with apache this would mean, to copy the whole Histoufolder to /var/www/

### Grafana
- Move the file `histou.js` in the `public/dashboards/` folder within Grafana.
- Depending on the URL the `index.php` is published on the webserver, change the Variable `var url = 'http://localhost/histou/';` in in the histou.js file. If you copied the Histoufolder to the root of your webserver, the default URL is perfect.

## Configuration
Here are some of the important config-options:

| Section       | Config-Key    | Meaning       |
| ------------- | ------------- | ------------- |
|general|socketTimeout|This timeout defines the duration in seconds, which Histou will wait for a response from the InfluxDB|
|general|phpCommand|This is the command which is used to call PHP on the commandline. If it's in the PATH php is mostly enough, if not write the full path to the file.
|folder|defaultTemplateFolder|This is the path to the folder containing the default templates|
|folder|customTemplateFolder|This is the path to the folder containing the custom templates. The templates in this folder will override files in the default folder, if they have the same filename|
|influxdb|influxFieldseperator|This char has to be the same as in the Nagflux-config. It separates the logical parts of the Tablename|

## Templates
There are two types of templates, the simple and the PHP. Simple templates are static, PHP are dynamic. Both cointains a rule, which describes on which host/service combination the template is uses. The type of the template is defined by its file-ending, `.simple` or `.php`

### Rule
A rule contains four keys: host, service, command and perfLabel. The values describes when the template is used. You can write for example an fixed hostname in the host field if the template should be uses just for one Host. It is also possible to use Regular Expressions in every field. Rules have a hierarchy on the to there is the host on the bottom the perfLabel. If a rule does not match in the hostname the perfLabel beneath will not be checked.

#### Typical Rules
- One rule for all ping checks, due to the fact that every check returns an pl and rta as perfLabel you can match on them
  - host: .*
  - service: .*
  - command: .*
  - perfLabel: rta, pl
- ping template just for the test systems
  - host: test-.*
  - service: .*
  - command: .*
  - perfLabel: rta, pl

### Simple
Simple templates contain a rule followed by an JSON object. The reason for this kind of template is, to over the user an easy way to create dashboards. The tradeoff is, that in the simple template it is not possible to use a template created for service1 to use on service2 if the perfLabels differ between those services.

### PHP
In the PHP template you can write PHP code which will be executed when the template gets chosen. You just have to return a JSON string or an object of the build-in PHP dashboard.

## DEMO
This Dockercontainer contains OMD and everything is preconfigured to use Nagflux/Histou/Grafana/InfluxDB: https://github.com/Griesbacher/docker-omd-grafana

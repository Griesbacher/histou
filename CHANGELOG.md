## v0.4.3 - 20.10.2017
### Features
- Sakuli customizations

## v0.4.2 - 15.09.2017
### Features
- Customize Grafana CSS

## v0.4.1 - 30.01.2017
### Fixes
- Changes "Null value" to "connected"

## v0.4.0 - 30.11.2016
### Features
- URL/Config param to disable paneltitles for bigger graphs. Url flag: disablePanelTitel - works now also on simple templates.
- URL param to specify a certain template. Take a look at the [README](https://github.com/Griesbacher/histou#url-parameters).
- URL param to disable Influxdb lookup. Take a look at the [README](https://github.com/Griesbacher/histou#url-parameters).

### Fixes
- Errormessage will be printed properly when simpletemplate has malformed json.

## v0.3.10 - 23.11.2016
### Features
- URL/Config param to disable paneltitles for bigger graphs. Url flag: disablePanelTitel

## v0.3.9 - 09.11.2016
### Features
- Support Counter results(uom unit is a 'c') within the default template

## v0.3.8 - 02.11.2016
### Features
- check_multi template

### Fixes
- Backslash in perflables

## v0.3.7 - 19.10.2016
### Features
- Execution of phpCommand is changed from system to proc_open - in most cases this should not change anything

### Fixes
- Can't use function return value in write context

## v0.3.6 - 13.10.2016
### Features
- Test if phpCommand from config is valid

## v0.3.5 - 23.09.2016
### Change
- Annotation function changed header

### Features
- Sakuli template
- Sakuli Images are displayed withing Grafana

### Fixes
- Missing Forecastfolder should not throw an exception

## v0.3.4 - 05.07.2016
### Features
- API to get forecast config added (experimental)

## v0.3.3 - 07.06.2016
### Fixes
- Simplefiles should work again, after layoutchange

## v0.3.2 - 07.06.2016
### Features
- Now PHP 5.3.3 upto PHP 7.04 should work

## v0.3.1 - 08.04.2016
### Features
- check_oracle/db2_heath templates

### Fixes
- Templating for Grafana 3 Beta2

## v0.3.0 - 01.04.2016
### Features
- Elasticsearch2 support
- Grafana 3.0 Beta1 support

### Breaks
- dashboards created before this version have to be updated, due to changes of functionnames


## v0.2.0 - 23.12.2015:
### Features
- syntax check on php templates, malformed templates will be ignored but an error appears in the apache error.log
- template cache, the valid templates will be cached
- default datasource is the name of the influxdb database from the config
- using php namespaces
- variables can be uses in rules. Available are: host, service, command. The variables values are from the database, they are written with the influxdbfieldseperator pre and pos e.g. &host&-lines.\* -> Nagiosserver-lines.\*
- grafana unit system is used to display nagios units

### Fixes
- multiple Grafana gaps
- simple template: naming problem
- star regex in perfLabel works, but an exact match wins against star
- downtime query warning within the query editor
- percentage in queries
- perfLabel sorting

### Breaks
- dashboards created before this version have to be updated, due to the namespace system and changes of functionnames

## v0.1.0 - 12.11.2015:
### Features
- Change Dashboard to Grafana v.2.5.0

### Fixes
- changed panelid counter start to 1
- change background color only on dashboard-solo

## v0.0.1 - 29.10.2015:
### Features
- Everything :wink:

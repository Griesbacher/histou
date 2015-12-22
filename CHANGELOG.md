## v0.2.0 - upcoming:
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

### Breaks
- dashboards created before this version have to be updated, du to the namespace system and change of functionnames

## v0.1.0 - 12.11.2015
### Features
- Change Dashboard to Grafana v.2.5.0

### Fixes
- changed panelid counter start to 1
- change background color only on dashboard-solo

## v0.0.1 - 29.10.2015
### Features
- Everything :wink:

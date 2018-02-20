# Faster Popular Posts Widgets for ClassiPress

This is a quick and dirty plugin to resolve an issue seen on heavily trafficked sites using the ClassiPress theme. We
were seeing long page loads (20s+) caused by frequent and expensive queries to the database. Using New Relic we
determined the issue was caused by a query called by ClassiPress' Popular Ads widgets.

This plugin creates two new widgets labeled as `Faster ClassiPress Top Ads Today` and `Faster ClassiPress Top Ads
Overall`. On page load, these widgets call cached data which is refreshed by an hourly cron. Using these widgets will
reduce the number of times these queries are run from multiple times per page load for every user on the site to just
once per hour.

**Note:** Given time constraints, the widgets are not feature complete. The `Today` widget is hardcoded to serve 10 ads and
`Overall` is hardcoded to serve 5 ads.

**Testing this change under load:** During a load test on Feb 19, these widgets no longer showed as a bottleneck in page load time.

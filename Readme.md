# Distribution for the Flexible plugin integration Workpackage

http://forge.typo3.org/issues/45013

## Installation

```
composer create-project workpackage/flexible-plugin-integration fpi
cd fpi
./flow gerrit:update
./flow doctrine:migrate
./flow site:import --package-key TYPO3.NeosDemoTypo3Org
./flow user:create admin joh316 max mustermann
```

Then you can go to to http://[my-domain]/setup to set up your database configuration
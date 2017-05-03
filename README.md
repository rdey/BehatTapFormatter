# BehatTapFormatter
A TAP (Test Anything Protocol) formatter for Behat

Based on [anho/BehatFormatterTeamcity](https://github.com/anho/BehatFormatterTeamcity).

## Usage

Add the extension to your `behat.yml` like this:

```yaml
default:
  extensions:
    Redeye\BehatTapFormatter\TapFormatterExtension: ~
```

The formatter is now registered with the name `tap`, which means you can use on the command line it as follows:

```
$ behat -f tap
```

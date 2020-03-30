# Release checklist

* [ ] Get master to the appropriate code release state.
      [Travis CI](https://travis-ci.org/digiaonline/lumen-cors)
      should be running cleanly for all merges to master.
      [![Build Status](https://travis-ci.org/digiaonline/lumen-cors.svg?branch=master)](https://travis-ci.org/digiaonline/lumen-cors)

* [ ] Update the CHANGELOG:

```bash
git checkout master
edit CHANGELOG.md
git add CHANGELOG.md
git commit -m "Update the CHANGELOG"
git push
```

* [ ] Tag with next version:

```bash
git tag 3.4.0
git push --tags
```

* [ ] After a minute or two, check the new version is at
      https://packagist.org/packages/nordsoftware/lumen-cors

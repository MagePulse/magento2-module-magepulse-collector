# Notes

Need to document the installation and configuration process

## Releasing a new version

1. Update the version in `etc/module.xml` (if needed).
2. Merge the changes to the `main` branch `git merge development --no-ff`.
3. Push the changes to the `main` branch `git push origin main`.
4. Wait for the CI to finish, this will do the following:
    1. Update the `composser.json` file version.
    2. Create the tag.
    3. Generate the `CHANGELOG.md`.
    4. Create the release on GitHub.
5. Pull the main branch back down `git pull`.
6. Merge the changes to the `development` branch `git checkout development && git merge main --no-ff` (make sure to
   add [skip ci] to the commit message).
7. Push the changes to the `development` branch `git push origin development`.

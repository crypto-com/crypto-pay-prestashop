rm ${PWD##*/}.zip
rm -rf cryptopay 
rsync -av --progress . cryptopay --exclude .git --exclude .gitignore --exclude .DS_Store --exclude publish.sh --exclude README.md
zip -r ${PWD##*/}.zip cryptopay
rm -rf cryptopay 
" hello this is demo conf
let g:sPathProject = expand('<sfile>:p:h')
let s:sPath = expand('<sfile>:p:h')
let g:indexer_ctagsCommandLineOptions=""
let g:indexer_indexerListFilename = g:sPathProject.'/.indexer_files'
let g:indexer_projectsSettingsFilename = g:sPathProject.'/vimproject'
" its ugly at the moment.
":Project .vimprj/vimproject

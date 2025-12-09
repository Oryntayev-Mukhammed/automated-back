<?php\n\=json_decode(file_get_contents('Modules/Blog/resources/data/posts.json'),true);\n\=0; foreach(\ as \){ if(!empty(\['slug']) && \['slug']!=='string') \++; }\necho \, '\n';\n

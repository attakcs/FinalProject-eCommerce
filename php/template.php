<?php
function RenderTemplate($path, $data){
    if(!file_exists($path)){
        return null;
    }

    $tpl = file_get_contents($path);

    // Replace flat variables
    foreach($data as $k=>$v){
        if(!is_array($v)){
            $tpl = str_replace('{'.$k.'}', $v, $tpl);
        }else{
            // Associative array
            foreach($v as $k1 => $v1){
                if(!is_array($v1)){
                    $tpl = str_replace('{'.$k.'.'.$k1.'}', $v1, $tpl);
                }
            };
        }
    }

    // Replace array variables (Repeat)
    preg_match_all("#\[repeat\s+?(.+?)=>(.+?)\](.*?)\[/repeat\]#s", $tpl, $matches, PREG_SET_ORDER);
    if(empty($matches)){
        return $tpl;
    }

    for($i = 0; $i < count($matches); $i++){
        $match = $matches[$i];
        $group = $match[1];
        $groupItem = $match[2];
        $segment = $match[3];

        $dataRepeat = [];
        // loop all data
        foreach($data[$group] as $obj){
            if(is_array($obj)){
                // Combine group item name with group item keys and enclose them within brackets
                $objKeys = array_keys($obj);
                $objKeys = array_map(function($i) use ($groupItem){
                    return '{'.$groupItem.'.'.$i.'}';
                }, $objKeys);

                $objValues = array_values($obj);
                $dataRepeat[] = str_replace($objKeys, $objValues, $segment);
            }else{
                $dataRepeat[] = str_replace('{'.$groupItem.'}', $obj, $segment);   
            }
        }

        $dataRepeat = implode("", $dataRepeat);
        $tpl = str_replace($match[0], $dataRepeat, $tpl);
    }
    
	//error_log($tpl, 3, './log.html');
    return $tpl;
}
?>
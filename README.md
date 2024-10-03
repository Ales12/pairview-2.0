# Pärchenübersicht
Bei der Pärchenübersicht können User ihre Pärchen eintragen und diese in verschiedene Kategorien, die im ACP vorher festgelegt wurden, eintragen. Hierbei kann das Team entscheiden, ob es hier schon ein bestehendes Profilfeld auslesen möchte oder aber ein seperates Bild hochgeladen werden muss. 

# link
misc.php?action=pairview

# datenbank
pairview

# templates
- pairview 	
- pairview_add 	
- pairview_cat 	
- pairview_menu 	
- pairview_options 	
- pairview_pairs 	
- pairview_pic_input

# css
*pairview.css*
```
.pairview_flex {
	display: flex;
	justify-content: center;
}

.pairview_flex > div{
	padding: 5px 10px;
	margin: 2px 10px;
}


.pairview_cat{
	background: #0f0f0f url(../../../images/tcat.png) repeat-x;
  color: #fff;
  border-top: 1px solid #444;
  border-bottom: 1px solid #000;
  padding: 7px;
}
.pairview_pairs{
	display: flex;
	align-items: center;
}


.pairview_pair{
	width: 45%;
	display: flex;
	justify-content: center;
	align-items: center;
	margin: 10px 20px;
	flex-wrap: wrap;
}

.pairview_lovers_pic{
	height: 100px;
	width: 100px;
	border-radius: 100%;
	padding: 5px;
	text-align: center;	
	border: 1px solid #0066a2;
}

.pairview_lovers_pic img{
		height: 100px;
	width: 100px;
	border-radius: 100%;

}

.pairview_lovers{
	padding: 5px 10px;
	text-align: center;
	position: relative;
	z-index: 2;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}

.lovername{
	width: 100%;	
}

.pairview_and{
  text-align: center;
  color:#0066a2;
  z-index: 1 !important;
  opacity: .2;
  font-size: 100px;
  position: absolute;
  text-indent: 0px;
  text-transform: uppercase;
}

.pairview_options{
	width: 100%;	
	text-align: center;
}
```

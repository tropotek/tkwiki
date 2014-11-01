
/**
 * Note You must supply the id value
 * 
 * @param id
 * @param target (optional)
 * @return void
 */
function lsetup(id, target)
{
    try {
        if (!target)
            target = this;

        var o_set = target.document.getElementById('lbContainerWH_'+id);
        var o_getH = target.document.getElementsByTagName('BODY')[0];

        o_set.style.height = o_getH.scrollHeight;
    } catch (e) {
    }
}

/**
 * Note You must supply the id value
 * 
 * @param id
 * @param target (optional)
 * @return boolean
 */
function lon(id, target)
{
    try {
        if (parent.visibilityToolbar)
            parent.visibilityToolbar.set_display("standbyDisplayNoControls");
    } catch (e) {}

    try {
        if (!target)
            target = this;
        lsetup(id, target);
        if (!target._fon_disabled_arr)
            target._fon_disabled_arr = new Array();
        else if (target._fon_disabled_arr.length > 0)
            return true;
        
        target.document.getElementById("lbContainer_"+id).style.display = "";
        var select_arr = target.document.getElementsByTagName("select");
        
        for (var i = 0; i < select_arr.length; i++) {
            if (select_arr[i].disabled)
                continue;

            select_arr[i].disabled = true;
            _fon_disabled_arr.push(select_arr[i]);
            var cfone = target.document.createElement("input");
            cfone.type = "hidden";
            cfone.name = select_arr[i].name;
            var values = new Array();
            for (var n = 0; n < select_arr[i].length; n++) {
                if (select_arr[i][n].selected) {
                    values[values.length] = select_arr[i][n].value;
                }
            }
            cfone.value = values.join(",");
            select_arr[i].parentNode.insertBefore(cfone, select_arr[i]);
        }
    } catch (e) {
        return false;
    }
    return true;
}

/**
 * Note You must supply the id value
 * 
 * @param id
 * @param target (optional)
 * @return boolean
 */
function loff(id, target)
{
    try {
        if (parent.visibilityToolbar) {
            parent.visibilityToolbar.set_display(visibilityCount
                                                 ? "standbyDisplay"
                                                 : "standbyDisplayNoControls");
        }
    } catch (e) {}

    try {
        if (!target)
            target = this;

        target.document.getElementById("lbContainer_"+id).style.display = "none";

        if (target._fon_disabled_arr) {
            while(_fon_disabled_arr.length > 0) {
                var select = _fon_disabled_arr.pop();
                select.disabled = false;

                var cfones_arr = target.document.getElementsByName(select.name);
                for (var n = 0; n < cfones_arr.length; n++) {
                    if ("hidden" == cfones_arr[n].type)
                        cfones_arr[n].parent.removeChild(cfones_arr[n]);
                }
            }
        }
    } catch (e) {
        return false;
    }
    return true;
}

/**
 * Created with JetBrains PhpStorm.
 * User: DELL
 * Date: 9/1/16
 * Time: 10:06 PM
 * To change this template use File | Settings | File Templates.
 */

var grid = {};

grid.form = null;

grid.page = null;

grid.sorter = {"field_name": null, "field_value": null};

grid.action_sort = function(column) {
    var field = $(column).attr("title");
    var dir = $(column).attr("dir");
    if (grid.sorter.field_name.val() == field) {
        if (grid.sorter.field_value.val() == "ASC") {
            dir = "DESC";
        } else {
            dir = "ASC";
        }
    }
    grid.sorter.field_name.val(field);
    grid.sorter.field_value.val(dir);
    if (grid.form != null) {
        grid.form.submit();
    }
    return false;
};

grid.go_page = function(number) {
    grid.page.val(parseInt(number));
    grid.form.submit();
    return false;
};

grid.first_page = function() {
    grid.page.val(1);
    grid.form.submit();
    return false;
};

grid.prev_page = function() {
    var page = parseInt(grid.page.val());
    page--;
    grid.page.val(page);
    grid.form.submit();
    return false;
};

grid.next_page = function(last_page) {
    var page = parseInt(grid.page.val());
    page++;
    if (page > last_page) {
        return false;
    }
    grid.page.val(page);
    grid.form.submit();
    return false;
};

grid.last_page = function(page) {
    grid.page.val(page);
    grid.form.submit();
    return false;
};

grid.check_all = function(checked, selector) {
    $(selector).each(function() {
        $(this).prop("checked", checked);
        var id = parseInt($(this).attr("data-id").toString());
        if (checked) {
            $(this).parent().addClass("checked");
            $(this).parent().parent().parent().addClass("warning");
            grid.add_selected(id);
        } else {
            $(this).parent().removeClass("checked");
            $(this).parent().parent().parent().removeClass("warning");
            grid.remove_selected(id);
        }
    });
    return true;
};

grid.check_item = function(item) {
    var $item = $(item);
    var id = parseInt($item.attr("data-id").toString());
    var checked = $item.prop("checked");
    if (checked) {
        $item.parent().parent().parent().addClass("warning");
        grid.add_selected(id);
    } else {
        $item.parent().parent().parent().removeClass("warning");
        grid.remove_selected(id);
    }
    return this;
};

grid.check_selected_item = function(item_selector, active_selector) {
    var $items = $(item_selector + ":checked");
    var $handler = $(active_selector);
    if ($items.length == 0) {
        $handler.prop("disabled", "disabled");
        return false;
    }
    $handler.prop("disabled", false);
    return false;
};

grid.redraw_selected = function(item_selector) {
    var items = $(item_selector + ":checkbox");
    $(items).each(function() {
        var checked = $(this).attr("checked");
        if (checked) {
            $(this).parent().parent().addClass("warning");
        } else {
            $(this).parent().parent().removeClass("warning");
        }
    });
};

grid.selected_ids = [];

grid.selected_input = null;

grid.add_selected = function(id) {
    if (grid.selected_ids.indexOf(id) < 0) {
        grid.selected_ids.push(id);
    }
    // console.log(grid.selected_ids);
    grid.update_selected();
    return this;
};

grid.remove_selected = function(id) {
    if (grid.selected_ids.indexOf(id) >= 0) {
        var index = grid.selected_ids.indexOf(id);
        grid.selected_ids.splice(index, 1);
    }
    // console.log(grid.selected_ids);
    grid.update_selected();
    return this;
};

grid.update_selected = function() {
    grid.selected_input.val(grid.selected_ids.join(','));
    return this;
};

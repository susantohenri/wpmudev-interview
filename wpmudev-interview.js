function henriWPMUDevDrawTbody() {
    const tbody = jQuery(`[name='table_thing'] tbody`)
    jQuery.post(wpmudev_interview_list.url, {
        keyword: jQuery(`[name='table_thing'] [name="keyword"]`).val(),
        order: jQuery(`[name='table_thing'] [name="order"]`).val(),
        perpage: jQuery(`[name='table_thing'] [name="perpage"]`).val(),
        page: jQuery(`[name='table_thing'] [name="page"]`).val(),
    }, data => {
        const pagination = jQuery(`[name='table_thing'] [name="page"]`)
        const selected_page = jQuery(`[name='table_thing'] [name="page"]`).val()
        pagination.html(``)
        for (var page = 1; page <= data.total_page; page++) {
            pagination.append(`<option value="${page}">${page}</option>`)
        }
        pagination.find(`option[value="${selected_page}"]`).attr(`selected`, true)

        tbody.html(``)
        for (var record of data.records) {
            tbody.append(`
                <tr>
                    <td>${record.name}</td>
                    <td>
                        <form method='POST'>
                            <input type='hidden' name='id' value='${record.id}'>
                            <input type='submit' name='read_thing' value='detail'>
                            <input type='submit' name='delete_thing' value='delete'>
                        </form>
                    </td>
                </tr>
            `)
        }
    })
}

jQuery(document).ready(() => {
    if (0 < jQuery(`[name='table_thing']`).length) {
        henriWPMUDevDrawTbody()
        jQuery(`[name='table_thing']`).find(`[name="search_button"]`).click(e => {
            e.preventDefault()
            henriWPMUDevDrawTbody()
        })
        jQuery(`[name='table_thing']`).find(`[name="order"],[name="perpage"],[name="page"]`).change(henriWPMUDevDrawTbody)
    }
})
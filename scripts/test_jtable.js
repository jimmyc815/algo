   $(document).ready(function () {

		var textInput = "select symbol, trade_date, buy_price from crsi_daily_buy_list1 where portfolio_id = 1 order by rank asc";

        $('#PersonTableContainer').jtable({
            title: 'Table of people',
            actions: {
                listAction: "scripts/test_jtable.php?action=list&txtInputQuery="+textInput,
                createAction: '/GettingStarted/CreatePerson',
                updateAction: '/GettingStarted/UpdatePerson',
                deleteAction: '/GettingStarted/DeletePerson'
            },
            fields: {
                symbol: {
					key: true,
                    title: 'Author Name',
                    width: '40%'
                },
                trade_date: {
                    title: 'Record date',
                    width: '30%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                buy_price: {
                    title: 'Age',
                    width: '20%'
                }
            }


        });

			//Load person list from server
			$('#PersonTableContainer').jtable('load');

    });

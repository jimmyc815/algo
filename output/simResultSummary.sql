select 'strat_id',  'avg_returns', 'sum_num_trades', 'avg_win_rate', 'avg_holding_days', 'enterCRSI', 'enterLimitOrderBelow', 'enterRange', 'exitCRSI'  union all select a.strat_id, a.avg_returns, a.sum_num_trades, a.avg_win_rate, a.avg_holding_days, a.entry_1 as 'EntryRSI', a.entry_2 as 'enterLimitOrderBelow', a.entry_3 as 'enterRange', a.entry_4 as 'exitRSI' from simResultSummary a, simInputs b where a.run_id = 12 and b.run_id = 11 INTO OUTFILE '/StockValuation/algo/output//test.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
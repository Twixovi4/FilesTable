```
public function show_files()
{
    // Получение данных сессии
    $session = session();
    $session_data = $session->get('logged_in');

    $worker_id = $session_data['worker_id'] ?? null;

    $data['inner_view'] = "filters";
    $limit_per_page = 20;

    $start_index = $this->request->getUri()->getSegment(3, 0);

    if ($start_index == 0) {
        $per_page = 0;
        $start_index = $limit_per_page;
    } else {
        $per_page = $start_index;
        $start_index = $start_index + $limit_per_page;
    }

    $total_records = $this->ask2sud_model->getTotalASA($worker_id);

    if ($total_records > 0) {
        $data['result'] = $this->ask2sud_model->ShowAskSudAccountLimit($worker_id, $per_page, $start_index);

        $pager = \Config\Services::pager();
        $pager->makeLinks($start_index / $limit_per_page + 1, $limit_per_page, $total_records);
        $data['pager'] = $pager;
    }

    return view('templates/header', $data)
         . view('ask2sud/show_file', $data)
         . view('templates/footer_no', $data);
}
```
<?php

return [
    /*
     * Row count above which an export runs as a background queued job
     * (GenerateReportExportJob, emailed as a signed download link) instead
     * of streaming an immediate download from the request itself.
     */
    'queue_threshold' => (int) env('REPORTS_QUEUE_THRESHOLD', 300),
];

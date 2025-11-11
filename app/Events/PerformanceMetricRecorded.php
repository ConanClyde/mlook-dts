<?php

namespace App\Events;

use App\Models\PerformanceMetric;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PerformanceMetricRecorded implements ShouldBroadcastNow
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public string $connection = 'sync';

	public PerformanceMetric $metric;

	public function __construct(PerformanceMetric $metric)
	{
		$this->metric = $metric;
	}

	public function broadcastOn(): array
	{
		return [
			new PrivateChannel('admin.performance'),
		];
	}

	public function broadcastAs(): string
	{
		return 'performance.metric.recorded';
	}

	public function broadcastWith(): array
	{
		return [
			'route' => $this->metric->route_name,
			'uri' => $this->metric->uri,
			'method' => $this->metric->method,
			'status_code' => $this->metric->status_code,
			'response_time_ms' => $this->metric->response_time_ms,
			'is_slow_request' => $this->metric->is_slow_request,
			'created_at' => $this->metric->created_at?->toISOString(),
		];
	}
}


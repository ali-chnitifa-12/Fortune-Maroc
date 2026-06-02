<div class="erp-page-wrap">
    @if($errors->any())
        <div class="fortune-error">
            <ul style="list-style:disc;margin-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="erp-card">
        <form method="POST" action="{{ $action }}">
            @csrf

            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="erp-form-grid">
                <div>
                    <label>Mapping Type</label>
                    <select id="mapping_type" name="mapping_type" required>
                        <option value="line" {{ old('mapping_type', $device?->mapping_type ?? 'line') === 'line' ? 'selected' : '' }}>
                            Line
                        </option>
                        <option value="machine" {{ old('mapping_type', $device?->mapping_type) === 'machine' ? 'selected' : '' }}>
                            Machine
                        </option>
                    </select>
                </div>

                <div>
                    <label>Zone</label>
                    <select name="zone_id">
                        <option value="">Select zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}"
                                {{ old('zone_id', $device?->zone_id) == $zone->id ? 'selected' : '' }}>
                                {{ $zone->code }} - {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Production Line</label>
                    <select name="production_line_id">
                        <option value="">Select production line</option>
                        @foreach($productionLines as $line)
                            <option value="{{ $line->id }}"
                                {{ old('production_line_id', $device?->production_line_id) == $line->id ? 'selected' : '' }}>
                                {{ $line->code }} - {{ $line->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="machine_box">
                    <label>Machine</label>
                    <select name="machine_id">
                        <option value="">Select machine</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}"
                                {{ old('machine_id', $device?->machine_id) == $machine->id ? 'selected' : '' }}>
                                {{ $machine->code }} - {{ $machine->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>ThingsBoard Device Name</label>
                    <input type="text"
                           name="device_name"
                           value="{{ old('device_name', $device?->device_name) }}"
                           required>
                </div>

                <div>
                    <label>Access Token</label>
                    <input type="text"
                           name="access_token"
                           value="{{ old('access_token', $device?->access_token) }}"
                           required>
                </div>

                <div>
                    <label>Status</label>
                    <select name="is_active" required>
                        <option value="1" {{ old('is_active', $device?->is_active ?? true) == 1 ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="0" {{ old('is_active', $device?->is_active ?? true) == 0 ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            <div class="erp-form-actions">
                <button type="submit" class="erp-btn erp-btn-primary">
                    {{ $buttonText }}
                </button>

                <a href="{{ route('thingsboard-devices.index') }}" class="erp-btn erp-btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@include('components.erp-page-style')

<script>
    function toggleMachineBox() {
        const type = document.getElementById('mapping_type').value;
        const machineBox = document.getElementById('machine_box');

        if (type === 'machine') {
            machineBox.style.display = 'block';
        } else {
            machineBox.style.display = 'none';
        }
    }

    document.getElementById('mapping_type').addEventListener('change', toggleMachineBox);
    toggleMachineBox();
</script>
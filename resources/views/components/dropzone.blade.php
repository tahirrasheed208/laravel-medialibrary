<input type="hidden" name="{{ $name }}" id="{{ $name }}">
<div id="{{ $dropzone_id }}" class="dropzone needsclick mb-3">
  <div class="dz-message needsclick">
    <button type="button" class="dz-button">{{ $message }}</button>
  </div>
</div>

@once
  @push(config('medialibrary.stack'))
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
  @endpush
@endonce

@push(config('medialibrary.stack'))
  <script>
    Dropzone.autoDiscover = false;
    const fileList = [];

    let myDropzone = new Dropzone("div#{{ $dropzone_id }}", {
      url: "{{ route('medialibrary.dropzone.upload') }}",
      addRemoveLinks: true,
      uploadMultiple: true,
      parallelUploads: 20,
      maxFiles: 20,
      maxFilesize: 3, // MB
      acceptedFiles: ".jpeg,.jpg,.png",
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }
    });

    myDropzone.on("success", function(file, serverFileName) {
      serverFileName.forEach(element => {
        if (element.file_name === file.upload.filename) {
          file.upload.filename = element.new_name;
          fileList.push(element.new_name);
        }
      });
    });

    myDropzone.on("complete", function(file) {
      if (file.status === 'error') {
        return;
      }

      document.getElementById('{{ $name }}').value = fileList.toString();
    });

    myDropzone.on("removedfile", function(file) {
      let file_name = file.upload.filename;

      const index = fileList.indexOf(file_name);
      if (index > -1) {
        fileList.splice(index, 1);
        document.getElementById('{{ $name }}').value = fileList.toString();
      }

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "{{ route('medialibrary.dropzone.delete') }}");
      xhr.setRequestHeader("Accept", "application/json");
      xhr.setRequestHeader("Content-Type", "application/json");
      xhr.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");

      let data = `{
        "file_name": "${file_name}"
      }`;

      xhr.send(data);
    });
  </script>
@endpush
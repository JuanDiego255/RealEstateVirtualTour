abstract class Resource<T> {}

class Loading<T> extends Resource<T> {}

class Success<T> extends Resource<T> {
  final T data;
  Success(this.data);
}

class AppError<T> extends Resource<T> {
  final String message;
  AppError(this.message);
}

// Returned when the server responds 409 Conflict (duplicate lead).
// leadId is the id of the already-existing lead.
class Duplicate<T> extends Resource<T> {
  final String message;
  final int leadId;
  Duplicate(this.message, this.leadId);
}

@extends('frontend.layout')

@section('content')
  <!-- Page title start-->
  <div class="page-title-area ptb-100">
    <!-- Background Image -->
    <img class="lazyload blur-up bg-img" src="assets/images/page-title-bg.jpg" alt="Bg-img">
    <div class="container">
      <div class="content">
        <h1>Profile</h1>
        <ul class="list-unstyled">
          <li class="d-inline"><a href="/">Home</a></li>
          <li class="d-inline">-</li>
          <li class="d-inline active">Profile</li>
        </ul>
      </div>
    </div>
  </div>
  <!-- Page title end-->

  <!-- Dashboard-area start-->
  <section class="user-dashboard pt-100 pb-70">
    <div class="container">
      <div class="row gx-xl-5">
        <div class="col-lg-3">
          <div class="widget-area radius-md">
            <div class="widget radius-md">
              <ul class="links">
                <li><a href="dashboard">Dashboard</a></li>
                <li><a href="order">My Orders </a></li>
                <li><a href="wishlist">My Wishlist </a></li>
                <li><a href="order-details">Orders Details</a></li>
                <li><a href="reset-password">Change Password </a></li>
                <li><a href="profile" class="active">Edit Profile </a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-9">
          <div class="row">
            <div class="col-lg-12">
              <div class="user-profile-details">
                <div class="account-info radius-md">
                  <div class="title">
                    <h3>Edit Profile</h3>
                  </div>
                  <div class="edit-info-area">
                    <form>
                      <div class="upload-img">
                        <div class="file-upload-area">
                          <div class="file-edit">
                            <input type='file' id="imageUpload" />
                            <label for="imageUpload"></label>
                          </div>
                          <div class="file-preview">
                            <div id="imagePreview">
                              <img class="lazyload bg-img" src="assets/images/avatar.jpg" alt="Avatar">
                            </div>
                          </div>
                        </div>
                        <div id="errorMsg"></div>
                      </div>
                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group mb-30">
                            <input type="text" class="form-control" placeholder="First Name" name="name" required>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group mb-30">
                            <input type="text" class="form-control" placeholder="Last Name" name="name" required>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group mb-30">
                            <input type="email" class="form-control" placeholder="Email" name="email" required>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <input type="text" class="form-control" placeholder="Phone" name="phone" required>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group mb-30">
                            <input type="text" class="form-control" placeholder="City" name="city" required>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group mb-30">
                            <select class="form-control" id="country">
                              <option value="America">America</option>
                              <option value="England">England</option>
                              <option value="Italy">Italy</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="custom-checkbox mb-30">
                            <input class="input-checkbox" type="checkbox" name="checkbox" id="checkbox3" value="">
                            <label class="form-check-label" for="checkbox3"><span>Male</span></label>
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="custom-radio mb-30">
                            <input class="input-radio" type="radio" name="radio" id="radio3" value="">
                            <label class="form-radio-label" for="radio3"><span>Female</span></label>
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-group mb-30">
                            <textarea name="address" class="form-control" placeholder="Address"></textarea>
                          </div>
                        </div>
                        <div class="col-lg-12 mb-15">
                          <div class="form-button">
                            <button type="submit" class="btn btn-lg btn-primary">Submit</button>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Dashboard-area end -->
@endsection

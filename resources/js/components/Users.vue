<template>
    <div class="content">
        <div class="row mt-5" v-if="$gate.isAdmin()">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Người dùng</h3>

                        <div class="card-tools">
                            <button class="btn btn-success" @click="newModal">Thêm người dùng mới <i class="fas fa-user-plus fa-fw"></i></button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tbody><tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registered at</th>
                                <th>Sửa đổi</th>
                            </tr>
                            <tr v-for="user in users" :key="user.id">
                                <td>{{user.id}}</td>
                                <td>{{user.name}}</td>
                                <td>{{user.email}}</td>
                                <td>{{user.type}}</td>
                                <td>{{user.created_at | myDate}}</td>
                                <td>
                                    <a href="#" @click="editModal(user)"><i class="fa fa-edit"></i></a>
                                    /
                                    <a href="#" @click="deleteUser(user.id)"><i class="fa fa-trash red"></i></a>
                                </td>
                            </tr>
                            </tbody></table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>

        <div v-if="$gate.isUser()">
            <not-found></not-found>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNewLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-show="!editmode" id="addNewLabel">Thêm người dùng mới</h5>
                        <h5 class="modal-title" v-show="editmode" id="addNewLabel">cập nhật thông tin người dùng</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="editmode ? updateUser() : createUser()">
                    <div class="modal-body">
                        <div class="form-group">
                            <input v-model="form.name" type="text" name="name"
                                   placeholder="nhập vào tên của bạn"
                                   class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                            <has-error :form="form" field="name"></has-error>
                        </div>

                        <div class="form-group">
                            <input v-model="form.email" type="email" name="email"
                                   placeholder="nhập vào email của bạn"
                                   class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                            <has-error :form="form" field="email"></has-error>
                        </div>

                        <div class="form-group">
                        <textarea v-model="form.bio" name="bio" id="bio"
                                  placeholder="Short bio for user (Optional)"
                                  class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                            <has-error :form="form" field="bio"></has-error>
                        </div>


                        <div class="form-group">
                            <select name="type" v-model="form.type" id="type" class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                <option value="">Select User Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">Standard User</option>
                                <option value="author">Author</option>
                            </select>
                            <has-error :form="form" field="type"></has-error>
                        </div>

                        <div class="form-group">
                            <input v-model="form.password" type="password" name="password" id="password"
                                   class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                            <has-error :form="form" field="password"></has-error>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
                        <button v-show="editmode" type="submit" class="btn btn-success">update</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Users",
        data(){
            return{
                editmode:false,
                users:{},
                form: new Form({
                    id:'',
                    name:'',
                    email:'',
                    password:'',
                    type:'',
                    bio:'',
                    photo:''
                })
            }
        },
        methods:{
            updateUser(id){
                this.$Progress.start();
                this.form.put('api/user/'+this.form.id)
                    .then(()=>{
                        $('#addNew').modal('hide');
                        swal.fire(
                            'update!',
                            'thông tin đã được cập nhật.',
                            'success'
                        )
                        this.$Progress.finish();
                        Fire.$emit('AfterCreated');
                    })
                    .catch(()=>{
                        this.$Progress.fail();
                    });
            },
            newModal(){
                this.editmode = false;
                this.form.reset();
                $('#addNew').modal('show');
            },
            editModal(user){
                this.editmode=true;
                this.form.reset();
                $('#addNew').modal('show');
                this.form.fill(user);
            },
            deleteUser(id){
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {

                    //ket qua khi xoa thanh cong
                    // Send request to the server
                    if (result.value) {
                        this.form.delete('api/user/'+id).then(()=>{
                            swal.fire(
                                'Deleted!',
                                'Your file has been deleted.',
                                'success'
                            )
                            Fire.$emit('AfterCreated');
                        }).catch(()=> {
                            swal.fire("Failed!", "There was something wronge.", "warning");
                        });
                    }
                })
            },
            loadUser(){
                if(this.$gate.isAdmin()) {
                    axios.get("api/user").then(({data}) => (this.users = data.data));
                }
            },
            createUser(){
                this.$Progress.start();
                this.form.post('api/user')
                    .then(()=>{
                        Fire.$emit('AfterCreated');
                        $('#addNew').modal('hide')
                        Toast.fire({
                            type: 'success',
                            title: 'Signed in successfully'
                        });
                        this.$Progress.finish();
                    })
                    .catch(()=>{})
            },
        },
        created(){
            this.loadUser();
            Fire.$on('AfterCreated',()=>{
                this.loadUser();
            });
            // setInterval(()=>this.loadUser(),3000);
        }
    }
</script>

<style scoped>

</style>
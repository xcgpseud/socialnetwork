<template>
    <div class="flex flex-col items-center">
        <div class="relative mb-8">
            <div class="w-100 h-64 overflow-hidden z-10">
                <img
                    src="https://www.nationalgeographic.com/content/dam/photography/photos/000/000/6.ngsversion.1467942028599.adapt.1900.1.jpg"
                    alt="user background image"
                    class="object-cover w-full"
                >
            </div>

            <div class="absolute flex items-center bottom-0 left-0 -mb-8 ml-12 z-20">
                <div class="w-32">
                    <img src="https://visualpharm.com/assets/387/Person-595b40b75ba036ed117da139.svg"
                         alt="profile picture"
                         class="object-cover w-32 h-32 border-4 border-gray-200 rounded-full shadow-lg"
                    >
                </div>

                <p class="ml-4 text-2xl text-gray-100">{{ user.data.attributes.name }}</p>
            </div>
        </div>

        <p v-if="postLoading">Loading posts...</p>
        <Post
            v-else
            v-for="post in posts.data"
            :key="post.data.post_id"
            :post="post"
        ></Post>

        <p v-if=" ! postLoading && posts.data.length < 1">No posts found.</p>
    </div>
</template>

<script>
    import Post from '../../components/Post';

    export default {
        name: "Show",

        components: {
            Post,
        },

        data: () => {
            return {
                user: null,
                posts: null,
                userLoading: true,
                postLoading: true,
            }
        },

        mounted() {
            axios.get(`/api/users/${this.$route.params.userId}`)
                .then(res => {
                    this.user = res.data;
                })
                .catch(error => {
                    console.log('Unable to fetch user.');
                })
                .finally(() => {
                    this.userLoading = false;
                });

            axios.get(`/api/users/${this.$route.params.userId}/posts`)
                .then(res => {
                    this.posts = res.data;
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.postLoading = false;
                });
        }
    }
</script>

<style scoped>

</style>

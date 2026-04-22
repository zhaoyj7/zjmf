/* 电子合同-甲方信息管理弹窗（用于finance甲方信息管理+插件签订页面signContract未完善信息需先完善再签订） */
const contractInfo = {
  template:
    `
        <el-dialog width="6.8rem" :visible.sync="isShowInfoDia" :show-close="false" @close="infoClose"
          class="contract-info-dialog">
          <div class="dialog-title">{{lang.finance_text59}}</div>
          <div class="dialog-dec">
            <p>{{lang.finance_text60}}</p>
            <p>{{lang.finance_text61}}</p>
          </div>
          <div class="dialog-box">
            <el-form :model="infoFormData" class="info-form" :rules="infoRules" ref="infoForm" label-position="top">
              <!-- <div class="certification-info" v-if="false">
                <div class="kd-item"><span class="kd-label">{{lang.finance_text62}}:</span>
                  <span class="kd-value" v-if="certificationObj.company.status === 1">{{certificationObj.company.certification_company}}</span>
                  <span class="kd-value" v-else-if="certificationObj.person.status === 1">{{certificationObj.person.card_name}}</span>
                </div>
                <div class="kd-item"><span class="kd-label">{{lang.finance_text63}}:</span>
                  <span class="kd-value" v-if="certificationObj.company.status === 1">{{certificationObj.company.company_organ_code}}</span>
                  <span class="kd-value" v-else-if="certificationObj.person.status === 1">{{certificationObj.person.card_number}}</span>
                </div>
              </div> -->
              <el-form-item :label="lang.finance_text64" prop="name">
                <el-input v-model="infoFormData.name" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text66" prop="id_number">
                <el-input v-model="infoFormData.id_number" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text67" prop="contact_phone">
                <el-input v-model="infoFormData.contact_phone" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text68" prop="contact_email">
                <el-input v-model="infoFormData.contact_email" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text69" prop="contact_address">
                <el-input v-model="infoFormData.contact_address" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <!-- 客户签章 -->
              <el-form-item :label="infoFormData.company_seal_required ? lang.finance_text149 : lang.finance_text159"
               prop="company_seal" :rules="infoFormData.company_seal_required ? infoRules.company_seal : []">
                <el-upload class="seal-upload" action="/console/v1/upload" :headers="{Authorization: jwt}"
                  :show-file-list="false" :on-success="handleSealSuccess" :on-preview="handleSealPreview"
                  :before-upload="beforeSealUpload" accept="image/jpeg,image/jpg,image/png">
                  <div class="seal-upload-content" v-if="infoFormData.company_seal_url">
                    <img :src="infoFormData.company_seal_url" class="seal-image">
                    <div class="seal-mask">
                      <div class="seal-mask-actions">
                        <i class="el-icon-zoom-in" @click.stop="handleSealPreview"></i>
                        <i class="el-icon-delete" @click.stop="handleSealDelete"></i>
                      </div>
                    </div>
                  </div>
                  <div class="seal-upload-placeholder" v-else>
                    <i class="el-icon-plus"></i>
                    <div class="seal-upload-text">{{lang.finance_text150}}</div>
                    <div class="seal-upload-tip">{{lang.finance_text151}}</div>
                  </div>
                </el-upload>
              </el-form-item>
              <!-- 图片预览对话框 -->
              <el-dialog :visible.sync="sealPreviewVisible" width="600px" append-to-body>
                <img :src="infoFormData.company_seal_url" style="width: 100%; display: block;">
              </el-dialog>
              <span class="first-save-tip" v-if="!infoFormData.is_save">{{lang.finance_text158}}</span>
            </el-form>
          </div>
          <div class="dialog-fotter">
            <el-button class="save-btn" @click="saveInfoData">{{lang.finance_text70}}</el-button>
            <el-button class="cancel-btn" @click="infoClose">{{lang.finance_text71}}</el-button>
          </div>
        </el-dialog>
      `,
  data () {
    return {
      isShowInfoDia: false,
      infoFormData: {
        name: "",
        id_number: "",
        contact_phone: "",
        contact_email: "",
        contact_address: "",
        company_seal: "",
        company_seal_url: "",
        is_save: false
      },
      infoRules: {
        name: [
          {
            required: true,
            message: lang.finance_text114,
            trigger: "blur",
          },
        ],
        id_number: [
          {
            required: true,
            message: lang.finance_text115,
            trigger: "blur",
          },
        ],
        contact_phone: [
          {
            required: true,
            message: lang.finance_text116,
            trigger: "blur",
          },
        ],
        contact_email: [
          {
            required: true,
            message: lang.finance_text117,
            trigger: "blur",
          },
        ],
        contact_address: [
          {
            required: true,
            message: lang.finance_text118,
            trigger: "blur",
          },
        ],
        company_seal: [
          {
            required: true,
            message: lang.finance_text156,
            trigger: "change",
          },
        ],
      },
      jwt: `Bearer ${localStorage.jwt}`,
      sealPreviewVisible: false, 
    };
  },
  props: {},
  created () {
    if (
      !document.querySelector(
        'link[href="' + url + 'components/contractInfo/contractInfo.css"]'
      )
    ) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = `${url}components/contractInfo/contractInfo.css`;
      document.head.appendChild(link);
    }
  },
  methods: {
    getContractInfo () {
      getPartInfo().then((res) => {
        this.infoFormData = { ...res.data.data };
        this.isShowInfoDia = true;
      });
    },
    infoClose () {
      this.isShowInfoDia = false;
      this.$refs.infoForm.resetFields();
    },
    saveInfoData () {
      this.$refs.infoForm.validate((valid) => {
        if (valid) {
          editPartInfo(this.infoFormData)
            .then((res) => {
              this.infoClose();
              this.$message.success(res.data.msg);
              this.$emit('sign-success');
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        }
      });
    },
    // 签章上传前验证
    beforeSealUpload (file) {
      const isJPGorPNG = file.type === 'image/jpeg' || file.type === 'image/jpg' || file.type === 'image/png';
      const isLt3M = file.size / 1024 / 1024 < 3;

      if (!isJPGorPNG) {
        this.$message.error(lang.finance_text152);
        return false;
      }
      if (!isLt3M) {
        this.$message.error(lang.finance_text153);
        return false;
      }
    },
    // 签章上传成功
    handleSealSuccess (res, file) {
      if (res.status === 200) {
        this.infoFormData.company_seal = res.data.save_name;
        this.infoFormData.company_seal_url = res.data.image_url;
        this.$message.success(lang.finance_text154);
      } else {
        this.$message.error(res.msg || lang.finance_text155);
      }
    },
    // 签章预览
    handleSealPreview () {
      this.sealPreviewVisible = true;
    },
    // 签章删除
    handleSealDelete () {
      this.infoFormData.company_seal = '';
      this.infoFormData.company_seal_url = '';
      this.$message.success(lang.finance_text157);
    },
  },
  mounted () { }
};
